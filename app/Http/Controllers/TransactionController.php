<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\FinanceHistory;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function __construct()
    {
        // cek apakah user sudah login via JWT
        $this->middleware('auth.jwt');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('itemsPerPage', 10);
        $search = $request->get('search');
        $source_id = $request->get('source_id');
        $finance_unique = $request->get('finance_unique');
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');
        $sort = $request->get('sort') ?? 'desc';

        $query = Transaction::with(['user', 'creator', 'finance']);

        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%")
                ->orWhere('source_id', 'LIKE', "%{$search}%");
        }

        if($source_id){
            if(auth()->user()->role !== 'superadmin'){
                return $this->response_access_failed();
            }

            $query->where('source_id', '=', $source_id);
        }

        if($finance_unique){
            if(auth()->user()->role !== 'superadmin'){
                return $this->response_access_failed();
            }

            $query->whereHas('finance', function ($q) use ($finance_unique) {
                $q->where('finance_unique', $finance_unique);
            });
        }

        if ($start_at && $end_at) {
            $start = Carbon::parse($start_at)->startOfDay();
            $end = Carbon::parse($end_at)->endOfDay();

            $query->whereBetween('created_at', [$start, $end]);
        } elseif ($start_at) {
            $start = Carbon::parse($start_at)->startOfDay();
            $query->where('created_at', '>=', $start);
        } elseif ($end_at) {
            $end = Carbon::parse($end_at)->endOfDay();
            $query->where('created_at', '<=', $end);
        }

        // batasi akses
        if (auth()->user()->role !== 'superadmin') {
            $query->where('user_id', '=',auth()->user()->id);
        }

        $query->each(function ($trans) {
            $trans->finance->makeHidden(['finance_number', 'created_by', 'user_id', 'id', 'finance_unique', 'updated_at']);
            // atau
            $trans->user->makeHidden(['id', 'created_at', 'updated_at', 'role']);
            $trans->creator->makeHidden(['id', 'created_at', 'updated_at', 'role']);
        });

        $transactions = $query->orderBy('created_at', $sort)->paginate($perPage);

        return $this->response_get_success([
            'transactions' => $transactions->items(),
            'total' => $transactions->total(),
            'user_id' => auth()->user()->id
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:225',
            'user_id' => 'required|exists:users,id',
            'amount' => 'numeric|required',
            'type' => 'nullable',
            'source_id' => 'required|exists:finances,id',
            'category' => 'required'
        ], [
            'title.required' => 'judul transaksi tidak boleh kosong',
            'title.max' => 'judul maksimal 225 karakter',
            'user_id.required' => 'target keuangan tidak boleh kosong',
            'user_id.exists' => 'pengguna tidak ditemukan',
            'amount.required' => 'input jumlah tidak boleh kosong',
            'amount.numeric' => 'input jumlah hanya boleh angka',
            'source_id.required' => 'sumber keuangan belum dipilih',
            'source_id.exists' => 'sumber keuangan tidak valid',
            'category.required' => 'kategori transaksi belum dipilih'
        ]);

        // Jika validasi gagal → kembalikan JSON error
        if ($validator->fails()) {
            return $this->response_validation_error($validator);
        }

        // cek apakah user benar pemilik finance
        $finance_user_exists = Finance::query()
            ->where('user_id', $request->user_id)
            ->first();

        if (!$finance_user_exists) {
            return $this->response_access_failed();
        }

        $request->type = $request->type ?? 'pengeluaran';

        try {
            DB::beginTransaction();
            // Create user
            $transaction = Transaction::create($request->all());

            if (!$transaction) {
                throw new \Exception('Gagal menyimpan data keuangan!.');
            }

            // update finance
            if ($request->type === 'pemasukan') {
                $updated = $finance_user_exists->update([
                    'amount' => (int) $finance_user_exists->amount + (int) $request->amount
                ]);

                $saved_history = FinanceHistory::create([
                    'finance_id' => $request->source_id,
                    'amount' => $request->amount,
                    'note' => $request->note,
                    'transaction_id' => $transaction->id
                ]);

                if (!$saved_history) {
                    throw new \Exception('Gagal menyimpan data riwayat keuangan');
                }
            } else {
                $updated = $finance_user_exists->update([
                    'amount' => (int) $finance_user_exists->amount - (int) $request->amount
                ]);
            }

            if (!$updated) {
                throw new \Exception('Gagal menyimpan data perubahan keuangan');
            }

            DB::commit();
            return $this->response_post_success(['transaction' => $transaction]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response_save_error($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id): JsonResponse
    {
        $transaction = Transaction::with(['user', 'finance', 'creator'])->find($id);

        if (!$transaction) {
            return $this->response_not_found();
        }

        // cek jika role tidak dirinya sendiri dan bukan role superadmin maka error
        if (auth()->user()->id != $transaction->user_id && auth()->user()->role != 'superadmin') {
            return $this->response_access_failed();
        }

        $transaction->makeHidden(['user_id', 'source_id', 'can_deleted', 'created_by', 'updated_at']);
        $transaction->finance->makeHidden(['finance_number', 'created_by', 'user_id', 'id', 'finance_unique', 'created_at', 'updated_at']);
            // atau
        $transaction->user->makeHidden(['id', 'updated_at']);
        $transaction->creator->makeHidden(['id', 'created_at', 'updated_at', 'role']);

        return $this->response_get_success([
            'transaction' => $transaction,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        /**
         * akan menghapus data history finance karena cascade
         */
        $transaction = Transaction::select(['id', 'user_id', 'source_id', 'type', 'amount'])->find($id);

        if (!$transaction) {
            return $this->response_not_found();
        }

        // cek jika user auth bukan superadmin dan
        if (auth()->user()->role !== 'superadmin') {
            if (auth()->user()->id !== $transaction->user_id) {
                return $this->response_access_failed();
            }
        }

        try {
            DB::beginTransaction();

            // update finance
            $finance = Finance::find($transaction->source_id);

            if(!$finance){
                throw new \Exception('Data dompet tidak ditemukan');
            }

            if ($transaction->type === 'pengeluaran') {
                $finance->amount += (int) $transaction->amount;

                if (!$finance->save()) {
                    throw new \Exception('Gagal merubah data keuangan');
                }
            } else {
                $amount = (int) $transaction->amount;

                if ((int) $finance->amount < $amount) {
                    throw new \Exception('Tidak bisa menghapus data transaksi.');
                } else {
                    $updated = $finance->update([
                        'amount' => (int) $finance->amount - (int) $transaction->amount
                    ]);

                    if (!$updated) {
                        throw new \Exception('Gagal merubah data keuangan');
                    }
                }
            }

            // hapus finance history
            $deleted_history = FinanceHistory::where('transaction_id', $transaction->id)->delete();

            if ($deleted_history === false) {
                throw new \Exception('Gagal menghapus data riwayat keuangan');
            }

            if (!$transaction->delete()) {
                throw new \Exception('Gagal menghapus data transaksi');
            }

            DB::commit();
            return $this->response_delete();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response_save_error(json_encode(['dompet' => $finance, 't' => $transaction]) ||$e->getMessage());
        }
    }

    public function count_transactions(Request $request): JsonResponse
    {
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');

        $query = Transaction::query();

        if ($start_at) {
            $query->whereDate('created_at', '>=', strtotime($start_at));
        }

        if ($end_at) {
            $query->whereDate('created_at', '<=', strtotime($end_at));
        }

        $transaction_count = $query->count();

        return response()->json($transaction_count);
    }

}

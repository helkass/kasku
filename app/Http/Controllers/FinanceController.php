<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\FinanceHistory;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FinanceController extends Controller
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
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');
        $user_id = $request->get('user_id');

        $query = Finance::with(['user', 'creator']);

        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        if ($user_id && $request->user()->role === 'superadmin') {
            $query->where('user_id', '=', $user_id);
        }

        if ($start_at && $end_at) {
            $query->whereBetween('DATE(created_at)', [
                date('Y-m-d', strtotime($start_at)),
                date('Y-m-d', strtotime($end_at))
            ]);
        } elseif ($start_at) {
            $query->whereDate('created_at', '>=', date('Y-m-d', strtotime($start_at)));
        } elseif ($end_at) {
            $query->whereDate('created_at', '<=', date('Y-m-d', strtotime($end_at)));
        }

        // batasi akses
        if ($request->user()->role !== 'superadmin') {
            $query->where('user_id', '=', $request->user()->id);
        }

        $finances = $query->orderBy('created_at', 'DESC')->paginate($perPage);

        $finances->getCollection()->transform(function ($finance) {
            $finance->makeHidden(['id', 'note', 'created_by', 'updated_at', 'finance_number']);

            if ($finance->user) {
                $finance->user->makeHidden(['id', 'updated_at', 'created_at', 'role']);
            }

            if ($finance->creator) {
                $finance->creator->makeHidden(['id', 'created_at', 'updated_at', 'role']);
            }

            return $finance;
        });


        $totalFinance = $finances->total();

        $results = [];

        // get total transactions
        foreach ($finances as $f) {
            $total = Transaction::where('source_id', '=', $f->id)->count();
            $daily_spent = Transaction::where('source_id', '=', $f->id)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
            $f->total_transactions = $total;
            $f->daily_spent = $daily_spent;
            $results[] = $f;
        }

        return $this->response_get_success([
            'finances' => $results,
            'total' => $totalFinance,
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
            'amount' => 'numeric|required|min:1',
            'daily_limit' => 'nullable|numeric',
            'note' => 'max:500|nullable'
        ], [
            'title.required' => 'judul transaksi tidak boleh kosong',
            'title.max' => 'judul maksimal 225 karakter',
            'user_id.required' => 'pemilik keuangan tidak boleh kosong',
            'user_id.exists' => 'pengguna tidak ditemukan',
            'amount.required' => 'jumlah saldo tidak boleh kosong',
            'amount.numeric' => 'jumlah saldo hanya boleh angka',
            'amount.min' => 'minimal saldo tidak boleh kurang dari 1',
            'daily_limit.numeric' => 'batas harian hanya boleh angka',
            'note.max' => 'maksimal catatan 500 karakter'
        ]);

        // Jika validasi gagal → kembalikan JSON error
        if ($validator->fails()) {
            return $this->response_validation_error($validator);
        }

        try {
            DB::beginTransaction();

            // cek duplicate title pada pengguna yang sama
            $is_duplicate = Finance::where('title', $request->title)
                ->where('user_id', $request->user_id)
                ->first();

            if ($is_duplicate) {
                throw new \Exception('Nama keuangan sudah digunakan!.');
            }

            // Create user
            $finance = Finance::create($request->all());

            if (!$finance) {
                throw new \Exception('Gagal menyimpan data keuangan!.');
            }

            $history_finance = FinanceHistory::create([
                'finance_id' => $finance->id,
                'amount' => $request->amount
            ]);

            if (!$history_finance) {
                throw new \Exception('Gagal menyimpan data history keuangan!.');
            }

            $transaction = Transaction::create([
                'user_id' => $request->user_id,
                'source_id' => $finance->id,
                'title' => 'Pembukaan Dompet',
                'type' => 'pemasukan',
                'amount' => $request->amount,
                'category' => 'umum',
                'can_deleted' => 0
            ]);

            if (!$transaction) {
                throw new \Exception('Gagal menyimpan data transaksi!.');
            }

            DB::commit();
            return $this->response_post_success(['finance' => $finance, 'history' => $history_finance]);
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
        $finance = Finance::with(['user', 'creator'])->where('finance_unique', '=',$id)->first();

        if (!$finance) {
            return $this->response_not_found();
        }

        // cek jika role tidak dirinya sendiri dan bukan role superadmin maka error
        if ($request->user()->id != $finance->user_id && $request->user()->role != 'superadmin') {
            return $this->response_access_failed();
        }

        return $this->response_get_success([
            'finance' => $finance,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        /**
         * akan menghapus data history finance karena cascade
         */

        $finance = Finance::where('finance_unique','=',$id)->first();

        if (!$finance) {
            return $this->response_not_found();
        }

        if (!$finance->delete()) {
            return $this->response_delete(false);
        }

        return $this->response_delete();
    }

    public function select2(Request $request): JsonResponse
    {
        $search = $request->get('search');

        $query = Finance::select('id', 'title', 'amount');

        // Jika ada pencarian
        if (!empty($search)) {
            $query->where(function ($q) use ($search, $request) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('amount', 'LIKE', "%{$search}%");

                // jika role bukan user admin
                if ($request->user()->role !== 'superadmin') {
                    $q->where('user_id', $request->user()->id);
                }
            });
        }

        $finances = $query->orderBy('title', 'ASC')->limit(20)->get();

        // Ubah format jadi Select2 compatible
        $results = $finances->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->title . ' - ' . number_format($item->amount, 0, ',', '.'),
                'title' => $item->title,
                'amount' => $item->amount,
            ];
        });

        return response()->json($results);
    }

    public function count_finances(Request $request): JsonResponse
    {
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');

        $query = Finance::query();

        if ($start_at) {
            $query->whereDate('created_at', '>=', $start_at);
        }

        if ($end_at) {
            $query->whereDate('created_at', '<=', $end_at);
        }

        $finance_count = $query->count();

        return response()->json($finance_count);
    }

    public function get_total_finance(): int|JsonResponse
    {
        $authenticatedUser = auth()->user();

        try {
            $total = Finance::where('user_id', $authenticatedUser->id)->sum('amount');
            return response()->json($total, 200);
        } catch (\Exception $e) {
            return $this->response_internal_server_error();
        }
    }

    public function get_income_expanse(Request $request): JsonResponse
    {
        $user_id = auth()->user()->id;
        $start_at = $request->start_at ?? date('Y-m-d');
        $end_at = $request->end_at ?? date('Y-m-d');

        $income = Transaction::where('user_id', '=', $user_id)
            ->where('type', '=', 'pemasukan')
            ->whereDate('created_at', '>=', $start_at)
            ->whereDate('created_at', '<=', $end_at)
            ->sum('amount');

        $expanse = Transaction::where('user_id', '=', $user_id)
            ->where('type', '=', 'pengeluaran')
            ->sum('amount');

        return response()->json([
            'income' => $income,
            'expanse' => $expanse
        ], 200);
    }

    public function get_fiinance_daily_expenses(Request $request)
    {
        try {
            $user = auth()->user();

            // Ambil semua finances milik user
            $finances = Finance::where('user_id', $user->id)->get();

            $result = [];

            foreach ($finances as $finance) {
                // Hitung pengeluaran per hari untuk finance ini
                $startOfWeek = now()->startOfWeek();
                $endOfWeek = now()->endOfWeek();

                // ambil total amount kemarin
                $yesterday = now()->subDay();
                $total_yesterday = Transaction::where('source_id', $finance->id)
                    ->whereDate('created_at', $yesterday)->sum('amount');

                $dailyExpenses = Transaction::where('source_id', $finance->id)
                    ->where('type', 'pengeluaran')
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->selectRaw('DAYNAME(created_at) as day_name, SUM(amount) as total_amount')
                    ->groupBy('day_name')
                    ->get()
                    ->keyBy('day_name');

                $monday = $dailyExpenses->get('Monday')->total_amount ?? 0;
                $tuesday = $dailyExpenses->get('Tuesday')->total_amount ?? 0 ?? 0;
                $wednesday = $dailyExpenses->get('Wednesday')->total_amount ?? 0;
                $thursday = $dailyExpenses->get('Thursday')->total_amount ?? 0;
                $friday = $dailyExpenses->get('Friday')->total_amount ?? 0;
                $saturday = $dailyExpenses->get('Saturday')->total_amount ?? 0;
                $sunday = $dailyExpenses->get('Sunday')->total_amount ?? 0;

                $total_expanses_today = $monday + $tuesday + $wednesday + $thursday + $friday + $saturday + $sunday;

                // calculate
                $percentage = 0;
                if ($total_yesterday == 0) {
                    if ($total_expanses_today == 0) {
                        $percentage = 0;    // Tidak ada pengeluaran sama sekali
                    } else {
                        $percentage = 100;  // Kemarin 0, hari ini ada pengeluaran = 100%
                    }
                } else {
                    $percentage = ($total_expanses_today / $total_yesterday) * 100;
                }

                // Format data untuk finance ini
                $financeData = [
                    'id' => $finance->id,
                    'title' => $finance->title,
                    'type' => $finance->type,
                    'daily_limit' => $finance->daily_limit, // Asumsi ada column daily_limit
                    'total' => $total_expanses_today,
                    'percentage_from_yesterday' => $percentage,
                    'days' => [
                        'monday' => $monday,
                        'tuesday' => $tuesday,
                        'wednesday' => $wednesday,
                        'thursday' => $thursday,
                        'friday' => $friday,
                        'saturday' => $saturday,
                        'sunday' => $sunday,
                    ]
                ];

                $result[] = $financeData;
            }

            return $this->response_get_success(['expanses' => $result]);

        } catch (\Exception $e) {
            return $this->response_internal_server_error();
        }
    }

}

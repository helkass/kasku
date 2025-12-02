<?php

namespace App\Http\Controllers;

use App\Models\FinanceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceHistoryController extends Controller
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
        $start_at = $request->get('start-at');
        $end_at = $request->get('end-at');

        $query = FinanceHistory::with(['finance', 'creator']);

        if ($search) {
            $query->where('finance_id', 'LIKE', "%{$search}%");
        }

        if ($start_at && $end_at) {
            $query->whereBetween('created_at', [
                date('Y-m-d H:i:s', strtotime($start_at)),
                date('Y-m-d H:i:s', strtotime($end_at))
            ]);
        } elseif ($start_at) {
            $query->where('created_at', '>=', date('Y-m-d H:i:s', strtotime($start_at)));
        } elseif ($end_at) {
            $query->where('created_at', '<=', date('Y-m-d H:i:s', strtotime($end_at)));
        }

        // batasi akses
        if ($request->user()->role !== 'superadmin') {
            $query->where('created_by', $request->user()->id);
        }

        $finance_histories = $query->orderBy('created_at', 'DESC')->paginate($perPage);

        return $this->response_get_success([
            'finances' => $finance_histories->items(),
            'total' => $finance_histories->total()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id): JsonResponse
    {
        $finance_history = FinanceHistory::with(['finance', 'creator'])->find($id);

        if (!$finance_history) {
            return $this->response_not_found();
        }

        // cek jika role tidak dirinya sendiri dan bukan role superadmin maka error
        if ($request->user()->id != $finance_history->created_by && $request->user()->role != 'superadmin') {
            return $this->response_access_failed();
        }

        return $this->response_get_success([
            'finance_history' => $finance_history,
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

        $finance_history = FinanceHistory::find($id);

        if (!$finance_history) {
            return $this->response_not_found();
        }

        if (!$finance_history->delete()) {
            return $this->response_delete(false);
        }

        return $this->response_delete();
    }
}

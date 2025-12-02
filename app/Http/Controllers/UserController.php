<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('itemsPerPage', 10);
        $search = $request->get('search');
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');

        $query = User::query();

        if ($search) {
            $query->where('username', 'LIKE', "%{$search}%");
        }

        if ($start_at) {
            $query->whereDate('created_at', '>=', $start_at);
        }

        if ($end_at) {
            $query->whereDate('created_at', '<=', $end_at);
        }

        // sembunyikan role superadmin
        $query->whereNotIn('role', ['superadmin']);

        $users = $query->orderBy('created_at', 'DESC')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data pengguna',
            'users' => $users->items(),
            'total' => $users->total()
        ], 200);
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
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
        ]);

        // Jika validasi gagal → kembalikan JSON error
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create user
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendaftaran data pengguna',
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak titemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data pengguna',
            'user' => $user,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Cari user
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan',
            ], 404);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username,' . $id,
            'password' => 'nullable|min:6'
        ], [
            'username.unique' => 'Username sudah digunakan pengguna lain',
            'password.min' => 'Password minimal 6 karakter'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update username
        $user->username = $request->username;

        // Jika password diisi, update password
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
            'user' => $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan!.',
            ], 404);
        }

        if (!$user->delete()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengguna!.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus!.',
        ], 200);
    }

    public function select2(Request $request): JsonResponse
    {
        $search = $request->get('search');

        $query = User::select('id', 'username');

        // Jika ada pencarian
        if (!empty($search)) {
            $query->where(function ($q) use ($search, $request) {
                $q->where('username', 'LIKE', "%{$search}%");
            });
        }

        $finances = $query->orderBy('username', 'ASC')->limit(20)->get();

        // Ubah format jadi Select2 compatible
        $results = $finances->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->username,
            ];
        });

        return response()->json([
            'results' => $results
        ]);
    }

    public function count_users(Request $request): JsonResponse
    {
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');

        $query = User::query();

        if ($start_at) {
            $query->whereDate('created_at', '>=', $start_at);
        }

        if ($end_at) {
            $query->whereDate('created_at', '<=', $end_at);
        }

        $query->where('role', '!=', 'superadmin');

        $user_count = $query->count();

        return response()->json($user_count);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $access_failed_message = 'Anda tidak memiliki izin untuk mengakses data ini';

    /**
     * Summary of response_access_failed
     * @return JsonResponse
     */
    protected function response_access_failed(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->access_failed_message,
        ], 403);
    }

    /**
     * Summary of response_not_found
     * @param string $message
     * @return JsonResponse
     */
    protected function response_not_found(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 404);
    }

    /**
     * Summary of response_validation_error
     * @param object $validator
     * @return JsonResponse
     */
    protected function response_validation_error(object|array $validator): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors(),
        ], 422);
    }

    /**
     * Summary of response_delete
     * @param bool $success
     * @return JsonResponse
     */
    protected function response_delete(bool $success = true): JsonResponse
    {
        $res_message = 'Berhasil menghapus data';
        $code = 200;

        if (!$success) {
            $res_message = 'Gagal menghapus data';
            $code = 500;
        }
        return response()->json([
            'success' => $success,
            'message' => $res_message,
        ], $code);
    }

    /**
     * Summary of response_save_error
     * @param string $message
     * @return JsonResponse
     */
    protected function response_save_error(string $message = 'Terjadi kesalahan saat menyimpan data')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 500);
    }

    protected function response_get_success(array|object $values): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Berhasil menampilkan data',
            ...((array) $values)
        ], 200);
    }

    protected function response_post_success(array|object $values): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan data',
            ...((array) $values)
        ], 201);
    }

    /**
     * Summary of response_bad_request
     * @param string $message
     * @return JsonResponse
     */
    protected function response_bad_request(string $message = 'Berhasil menambahkan data'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 400);
    }

    /**
     * Summary of response_internal_server_error
     * @param string $message
     * @return JsonResponse
     */
    protected function response_internal_server_error(string $message = 'Terjadi kesalahan server'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 400);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Autentikasi",
 *     description="API untuk registrasi dan login pengguna.",
 *     @OA\Contact(
 *         email="contact@company.com"
 *     )
 * )
 */


class Controller extends BaseController
{
    
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    protected function baseResponse(string $message, $detailError, $data = null, int $statusCode = 200): JsonResponse
    {
        $processedError = env('APP_DEBUG', false) ? $detailError : 'Production Level';

        $response = [
            'status' => $statusCode < 400 ? 'success' : 'error',  
            'message' => $message,
            'detailError'=>$processedError,
            'data' => $data, 
        ];

        return response()->json($response, $statusCode);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UserRegister;


class AuthController extends Controller
{

    /**
 * @OA\SecurityScheme(
 *     securityScheme="BearerToken",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT", 
 *     description="Gunakan token ini di header Authorization: Bearer {token}"
 * )
 */
    /**
 * @OA\Get(
 *     path="/api/v1/check-token/",
 *     summary="Periksa Validitas Token",
 *     description="Memeriksa apakah token autentikasi valid.",
 *     security={{"BearerToken":{}}}, 
 *     @OA\Response(
 *         response=200,
 *         description="Token valid",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Token Valid"),
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Token invalid",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Token invalid"),
 *             @OA\Property(property="status", type="string", example="error")
 *         )
 *     )
 * )
 */


    public function CheckToken(){
        if (auth()->check()) {
            // Jika Token Ada
            return $this->baseResponse('Token Valid', null, auth()->user(), 200);
        }
        // Jika Token Tidak Ada
        return $this->baseResponse('Token invalid', 'Gagal', auth()->user(), 401);
    }

    
    
    public function Register(UserRegister $request)
    {
        $validatedData = $request->validated();
    
        
        Log::channel('single')->info('Proses pendaftaran dimulai untuk email: ' . $validatedData['email']);
    
        DB::beginTransaction();
    
        try {
        
            if (User::where('email', $validatedData['email'])->exists()) {
            
                Log::channel('single')->warning('Email sudah terdaftar: ' . $validatedData['email']);
    
                return $this->baseResponse(
                    'Email sudah digunakan.',
                    'Email ' . $validatedData['email'] . ' telah terdaftar.',
                    null,
                    400
                );
            }
    
            // Membuat pengguna baru
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);
    
            DB::commit();
    
        
            Log::channel('single')->info('Pendaftaran pengguna berhasil untuk email: ' . $validatedData['email']);
    
            return $this->baseResponse(
                'Sukses mendaftarkan pengguna.',
                'Pengguna berhasil didaftarkan.',
                $user,
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
    
            Log::channel('single')->error('Gagal mendaftarkan pengguna untuk email: ' . $validatedData['email'] . '. Error: ' . $e->getMessage());
    
            return $this->baseResponse(
                'Gagal mendaftarkan pengguna.',
                $e->getMessage(),
                null,
                500
            );
        }
    }
     

    public function LoginUser(Request $request)
    {
        Log::channel('single')->info('Proses login dimulai untuk email: ' . $request->email);
    
        $checkuser = User::where('email', $request->email)->first();
    
        if (!$checkuser || !Hash::check($request->password, $checkuser->password)) {
            Log::channel('single')->warning('Login gagal untuk email: ' . $request->email . '. Penyebab: Email atau password salah.');
    
            return $this->baseResponse('Gagal Login', 'Unauthorized', $request->all(), 401);
        }
    
        Log::channel('single')->info('Login berhasil untuk email: ' . $request->email);
    
        $token = $checkuser->createToken('scan')->plainTextToken;
        $data = [
            'data' => $checkuser,
            'token' => $token,
        ];
    
        Log::channel('single')->info('Token berhasil dibuat untuk email: ' . $request->email);
    
        return $this->baseResponse('Login Sukses', null, $data, 200);
    }
}

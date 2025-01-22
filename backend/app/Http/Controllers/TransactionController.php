<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Reminder;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Gunakan token autentikasi Bearer untuk mengakses endpoint API."
 * )
 */

class TransactionController extends Controller
{

     public function createTransaction(Request $request)
     {   
         $validator = \Validator::make($request->all(), [
             'id_user' => 'required|exists:users,id',   
             'id_category' => 'required|exists:categories,id',   
             'amount' => 'required|numeric',  
             'description' => 'required|string|max:255',  
         ]);
     
         if ($validator->fails()) {
             return $this->baseResponse(
                 'Input tidak lengkap atau tidak valid.',
                 $validator->errors(),
                 null,
                 400 
             );
         }
     
         $check_balance = User::find($request->id_user);
         $balance = $check_balance->balance;
     
         $category_id = Category::find($request->id_category);
         $checktype = Type::find($category_id->id_type);
         
         if ($checktype->id != 3 && $balance == 0) {
             return $this->baseResponse(
                 'Gagal membuat transaksi.',
                 'Saldo pengguna tidak mencukupi untuk transaksi ini.',
                 null,
                 400 
             );
         }
         if($checktype->id != 3 && $balance <= $request->amount){
             return $this->baseResponse(
                 'Gagal membuat transaksi.',
                 'Saldo pengguna tidak mencukupi untuk transaksi ini.',
                 null,
                 400 
             );
         }
     
         Log::channel('single')->info('Proses pembuatan transaksi dimulai untuk user_id: ' . $request->id_user);
     
         DB::beginTransaction();  
     
         try {
             $transaksi = Transactions::create([
                 'id_user' => $request->id_user,
                 'id_category' => $request->id_category,
                 'id_type' => $checktype->id,
                 'amount' => $request->amount,
                 'description' => $request->description,
                 'date' => Carbon::now()->format('Y-m-d'),
             ]);
     
             if($checktype->id == 3){
                 $update_balance = $balance + $request->amount;
                 $check_balance->balance = $update_balance;
                 $check_balance->save();
             }else{
                 $update_balance = $balance - $request->amount;
                 $check_balance->balance = $update_balance;
                 $check_balance->save();
             }
     
             DB::commit(); 
     
             Log::channel('single')->info('Transaksi berhasil dibuat untuk user_id: ' . $request->id_user);
     
             return $this->baseResponse(
                 'Sukses membuat transaksi.',
                 'Transaksi berhasil ditambahkan.',
                 $transaksi,
                 201 
             );
     
         } catch (\Exception $e) {
             DB::rollBack(); 
     
             Log::channel('single')->error('Gagal membuat transaksi untuk user_id: ' . $request->id_user . '. Error: ' . $e->getMessage());
     
             return $this->baseResponse(
                 'Gagal membuat transaksi.',
                 $e->getMessage(),
                 null,
                 500 
             );
         }
     }

     
    public function createType(Request $request)
    {
        Log::channel('single')->info('Proses pembuatan type dimulai dengan nama: ' . $request->type);

        $request->validate([
            'type' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $type = Type::create([
                'type' => $request->type,
            ]);

            DB::commit(); 

            Log::channel('single')->info('Type berhasil dibuat: ' . $request->type);

            return $this->baseResponse(
                'Sukses mendaftarkan Type.',
                'berhasil ditambahkan.',
                $type,
                201 
            );
        } catch (\Exception $e) {
            DB::rollBack(); 

            Log::channel('single')->error('Gagal membuat type. Error: ' . $e->getMessage());

            return $this->baseResponse(
                'Gagal Tambahkan.',
                $e->getMessage(),
                null,
                500 
            );
        }
    }

     public function getType(Request $request)
     {
         Log::channel('single')->info('Proses pengambilan type dimulai.');
     
         $type = Type::get();
     
         if ($type->isEmpty()) {
             Log::channel('single')->warning('Type tidak ditemukan.');
     
             return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
         }
     
         Log::channel('single')->info('Type berhasil ditemukan: ' . $type->count() . ' type ditemukan.');
     
         return $this->baseResponse(
             'Sukses Mendapatkan Type.',
             'berhasil ditambahkan.',
             $type,
             200 
         );
     }

     public function createBudget(Request $request)
     {
         Log::channel('single')->info('Proses pembuatan anggaran dimulai untuk user_id: ' . $request->id_user . ', category_id: ' . $request->id_category);
     
         $validator = \Validator::make($request->all(), [
             'id_category' => 'required', 
         ]);
     
         if ($validator->fails()) {
             Log::channel('single')->warning('Validasi gagal. Input tidak valid atau tidak lengkap untuk user_id: ' . $request->id_user);
     
             return $this->baseResponse(
                 'Input tidak lengkap atau tidak valid.',
                 $validator->errors(),
                 null,
                 400 
             );
         }
     
         DB::beginTransaction();
     
         try {
             $budgets = Budget::create([
                 'id_category' => $request->id_category,
                 'amount' => $request->amount,
                 'id_user' => $request->id_user,
             ]);
             
             Log::channel('single')->info('Anggaran berhasil dibuat untuk user_id: ' . $request->id_user . ' dengan amount: ' . $request->amount);
     
             $transactionResponse = $this->createTransaction($request);
             if ($transactionResponse->status() !== 201) {
                 DB::rollBack();
                 Log::channel('single')->warning('Transaksi gagal, rollback dilakukan untuk user_id: ' . $request->id_user);
                 return $transactionResponse;
             }
     
             DB::commit();
     
             Log::channel('single')->info('Anggaran berhasil diperbarui dan transaksi dibuat untuk user_id: ' . $request->id_user);
     
             return $this->baseResponse(
                 'Sukses membuat transaksi.',
                 'Anggaran berhasil diperbarui.',
                 $budgets,
                 201 
             );
         } catch (\Exception $e) {
             DB::rollBack();
     
             Log::channel('single')->error('Proses pembuatan anggaran gagal untuk user_id: ' . $request->id_user . '. Error: ' . $e->getMessage());
     
             return $this->baseResponse(
                 'Gagal.',
                 $e->getMessage(),
                 null,
                 500 
             );
         }
     }

     public function refundBudget(Request $request)
     {
         Log::channel('single')->info('Proses refund anggaran dimulai untuk user_id: ' . $request->id_user . ' dan budget_id: ' . $request->id_budget);
     
         DB::beginTransaction();
     
         try {
             $refund = Budget::find($request->id_budget);
             $refund->status = false;
             $refund->save();
     
             Log::channel('single')->info('Refund anggaran berhasil untuk budget_id: ' . $request->id_budget . ' dengan amount: ' . $refund->amount);
     
             $category_id = Category::find($request->id_category);
             $checktype = Type::find($category_id->id_type);
     
             $trans = $request->merge([
                 'id_user' => $request->id_user,
                 'id_category' => $request->id_category,
                 'id_type' => $checktype->id,
                 'amount' => $refund->amount,
                 'description' => 'Refund Budget Sebesar ' . $refund->amount,
                 'date' => Carbon::now()->format('Y-m-d'),
             ]);
             $transactionResponse = $this->createTransaction($trans);
     
             if ($transactionResponse->status() !== 201) {
                 DB::rollBack();
                 Log::channel('single')->warning('Transaksi refund gagal untuk user_id: ' . $request->id_user);
                 return $transactionResponse;
             }
     
             DB::commit();
             Log::channel('single')->info('Transaksi refund berhasil untuk user_id: ' . $request->id_user);
     
             return $this->baseResponse(
                 'Sukses membuat transaksi.',
                 'Anggaran berhasil diperbarui.',
                 $refund,
                 201
             );
         } catch (\Exception $e) {
             DB::rollBack();
             Log::channel('single')->error('Proses refund anggaran gagal untuk user_id: ' . $request->id_user . ' dengan error: ' . $e->getMessage());
     
             return $this->baseResponse(
                 'Gagal.',
                 $e->getMessage() . $e->getLine(),
                 null,
                 500
             );
         }
     }

     public function payReminders(Request $request)
     {
         Log::channel('single')->info('Proses pembayaran reminder dimulai untuk user_id: ' . $request->id_user . ' dengan reminder_id: ' . $request->id_reminder);
     
         DB::beginTransaction();
     
         try {
             $payReminder = Reminder::find($request->id_reminder);
             $payReminder->status = true;
             $payReminder->save();
     
             Log::channel('single')->info('Reminder berhasil dibayar untuk reminder_id: ' . $request->id_reminder);
     
             $category_id = Category::find($request->id_category);
             $checktype = Type::find($category_id->id_type);
     
             $trans = $request->merge([
                 'id_user' => $payReminder->id_user,
                 'id_category' => $request->id_category,
                 'id_type' => $checktype->id,
                 'amount' => $payReminder->amount,
                 'description' => $payReminder->title . ' ' . $payReminder->due_date,
                 'date' => Carbon::now()->format('Y-m-d'),
             ]);
     
             $transactionResponse = $this->createTransaction($trans);
     
             if ($transactionResponse->status() !== 201) {
                 DB::rollBack();
                 Log::channel('single')->warning('Transaksi pembayaran reminder gagal untuk user_id: ' . $request->id_user);
                 return $transactionResponse;
             }
     
             DB::commit();
             Log::channel('single')->info('Transaksi pembayaran reminder berhasil untuk user_id: ' . $request->id_user);
     
             return $this->baseResponse(
                 'Sukses membuat transaksi.',
                 'Anggaran berhasil diperbarui.',
                 $payReminder,
                 201
             );
         } catch (\Exception $e) {
             DB::rollBack();
             Log::channel('single')->error('Proses pembayaran reminder gagal untuk user_id: ' . $request->id_user . ' dengan error: ' . $e->getMessage());
     
             return $this->baseResponse(
                 'Gagal.',
                 $e->getMessage() . $e->getLine(),
                 null,
                 500
             );
         }
     }
    

}

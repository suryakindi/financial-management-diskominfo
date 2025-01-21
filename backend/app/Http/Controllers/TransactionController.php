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


    

     public function createCategory(Request $request)
     {
         Log::channel('single')->info('Proses pembuatan kategori dimulai dengan nama: ' . $request->category);
     
         $request->validate([
             'category' => 'required',
         ]);
     
         DB::beginTransaction();
         try {
             $category = Category::create([
                 'category' => $request->category,
                 'id_type' => $request->id_type,
             ]);
     
             DB::commit(); 
     
             Log::channel('single')->info('Kategori berhasil dibuat: ' . $request->category);
     
             return $this->baseResponse(
                 'Sukses mendaftarkan Category.',
                 'berhasil ditambahkan.',
                 $category,
                 201 
             );
         } catch (\Exception $e) {
             DB::rollBack(); 
     
             Log::channel('single')->error('Gagal membuat kategori. Error: ' . $e->getMessage());
     
             return $this->baseResponse(
                 'Gagal Tambahkan.',
                 $e->getMessage(),
                 null,
                 500 
             );
         }
     }

     public function getCategory(Request $request)
     {
         Log::channel('single')->info('Proses pengambilan kategori dimulai.');
     
         $category = Category::get();
     
         if ($category->isEmpty()) {
             Log::channel('single')->warning('Kategori tidak ditemukan.');
     
             return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
         }
     
         Log::channel('single')->info('Kategori berhasil ditemukan: ' . $category->count() . ' kategori ditemukan.');
     
         return $this->baseResponse(
             'Sukses Mendapatkan Category.',
             'berhasil ditambahkan.',
             $category,
             200 
         );
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


     public function getMonthly(Request $request)
     {
         Log::channel('single')->info('Proses pengambilan transaksi bulanan dimulai untuk user_id: ' . $request->user_id . ' bulan: ' . $request->month . ' tahun: ' . $request->year);
     
         $validator = \Validator::make($request->all(), [
             'user_id' => 'required|exists:users,id',   
             'month' => 'required|integer|between:1,12', 
             'year' => 'required|integer',
         ]);
     
         if ($validator->fails()) {
             Log::channel('single')->warning('Input tidak valid atau tidak lengkap untuk user_id: ' . $request->user_id);
     
             return $this->baseResponse(
                 'Input tidak lengkap atau tidak valid.',
                 $validator->errors(),
                 null,
                 400
             );
         }
     
         $month = $request->month;
         $year = $request->year;
     
         $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
         $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
     
         $transaksi = Transactions::where('id_user', $request->user_id)
             ->whereBetween('date', [$startOfMonth, $endOfMonth])
             ->leftJoin('categories', 'transactions.id_category', '=', 'categories.id')
             ->leftJoin('types', 'transactions.id_type', '=', 'types.id')
             ->leftJoin('users', 'transactions.id_user', '=', 'users.id')
             ->select(
                 'transactions.*',
                 'categories.category as category_name',
                 'types.type as type_name',
                 'users.name as user_name',
                 'users.balance as user_balance'
             )
             ->get();
     
         if ($transaksi->isEmpty()) {
             Log::channel('single')->warning('Tidak ada transaksi ditemukan untuk user_id: ' . $request->user_id . ' bulan: ' . $month . ' tahun: ' . $year);
     
             return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
         }
     
         $total_income = 0;
         $total_expense = 0;
     
         foreach ($transaksi as $tran) {
             if ($tran->id_type == 3) {
                 $total_income += $tran->amount;
             }
     
             if ($tran->id_type == 1) {
                 $total_expense += $tran->amount;
             }
         }
     
         $balance = User::find($request->user_id);
     
         $dataResult = [
             'month' => $month . ' ' . $year,
             'total_income' => $total_income,
             'total_expense' => $total_expense,
             'balance' => $balance->balance,
             'detail_data' => $transaksi, 
         ];
     
         Log::channel('single')->info('Data transaksi berhasil ditemukan untuk user_id: ' . $request->user_id . ' bulan: ' . $month . ' tahun: ' . $year);
     
         return $this->baseResponse(
             'Transaksi ditemukan.',
             'Data transaksi untuk user ID ' . $request->user_id . ' pada bulan ' . $month . ' tahun ' . $year,
             $dataResult,
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
     
     public function createReminders(Request $request)
     {
         Log::channel('single')->info('Proses pembuatan reminder dimulai untuk user_id: ' . $request->id_user . ' dengan title: ' . $request->title);
     
         $validator = \Validator::make($request->all(), [
             'id_user' => 'required|exists:users,id',
             'title' => 'required|string|max:255',
             'amount' => 'required|numeric|min:1',
             'due_date' => 'required|date',
         ]);
     
         if ($validator->fails()) {
             Log::channel('single')->warning('Validasi gagal untuk user_id: ' . $request->id_user . '. Input tidak valid atau tidak lengkap.');
     
             return $this->baseResponse(
                 'Input tidak lengkap atau tidak valid.',
                 $validator->errors(),
                 null,
                 400
             );
         }
     
         DB::beginTransaction();
         try {
             $reminder = Reminder::create([
                 'id_user' => $request->id_user,
                 'title' => $request->title,
                 'amount' => $request->amount,
                 'due_date' => $request->due_date,
             ]);
             DB::commit();
     
             Log::channel('single')->info('Reminder berhasil dibuat untuk user_id: ' . $request->id_user);
     
             return $this->baseResponse(
                 'Sukses membuat pengingat.',
                 'Reminder berhasil dibuat.',
                 $reminder,
                 201
             );
         } catch (\Exception $e) {
             DB::rollBack();
             Log::channel('single')->error('Proses pembuatan reminder gagal untuk user_id: ' . $request->id_user . ' dengan error: ' . $e->getMessage());
     
             return $this->baseResponse(
                 'Gagal membuat pengingat.',
                 $e->getMessage(),
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
     

     public function getBudget(Request $request)
     {
         Log::channel('single')->info('Proses mengambil anggaran dimulai untuk user_id: ' . $request->id_user);
     
         $budgets = Budget::where('id_user', $request->id_user)
             ->leftJoin('categories', 'budgets.id_category', '=', 'categories.id')
             ->leftJoin('users', 'budgets.id_user', '=', 'users.id')
             ->select(
                 'budgets.*',
                 'categories.id as id_category',
                 'categories.category as category_name',
                 'users.name as user_name'
             )
             ->get();
     
         if ($budgets->isEmpty()) {
             Log::channel('single')->info('Tidak ada anggaran ditemukan untuk user_id: ' . $request->id_user);
             
             return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
         }
     
         Log::channel('single')->info('Berhasil mendapatkan anggaran untuk user_id: ' . $request->id_user . ' dengan jumlah anggaran: ' . $budgets->count());
     
         return $this->baseResponse(
             'Sukses Mendapatkan budgets.',
             'berhasil.',
             $budgets,
             200 
         );
     }

     public function GetReminder(Request $request)
     {
         Log::channel('single')->info('Proses mengambil reminder dimulai untuk user_id: ' . $request->id_user);
     
         $reminder = Reminder::where('id_user', $request->id_user)
             ->leftJoin('users', 'reminders.id_user', '=', 'users.id')
             ->select(
                 'reminders.*',
                 'users.name as user_name'
             )
             ->get();
     
         if ($reminder->isEmpty()) {
             Log::channel('single')->info('Tidak ada reminder ditemukan untuk user_id: ' . $request->id_user);
             
             return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
         }
     
         Log::channel('single')->info('Berhasil mendapatkan reminder untuk user_id: ' . $request->id_user . ' dengan jumlah reminder: ' . $reminder->count());
     
         return $this->baseResponse(
             'Sukses Mendapatkan reminder.',
             'berhasil ditambahkan.',
             $reminder,
             200 
         );
     }
     
     public function checkReminder(Request $request)
     {
         $today = now()->toDateString(); 
         Log::channel('single')->info('Proses cek reminder untuk user_id: ' . $request->id_user . ' pada tanggal: ' . $today);
     
         $reminder = Reminder::where('id_user', $request->id_user)
             ->where('due_date', $today) 
             ->where('status', false) 
             ->leftJoin('users', 'reminders.id_user', '=', 'users.id')
             ->select(
                 'reminders.*',
                 'users.name as user_name'
             )
             ->get();
         
         if ($reminder->isEmpty()) {
             Log::channel('single')->info('Tidak ada reminder ditemukan untuk user_id: ' . $request->id_user . ' pada tanggal: ' . $today);
     
             return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
         }
     
         Log::channel('single')->info('Berhasil mendapatkan reminder untuk user_id: ' . $request->id_user . ' pada tanggal: ' . $today . ' dengan total reminder: ' . $reminder->count());
     
         $resultReminder = [
             'detail' => $reminder,
             'total' => $reminder->count(),
         ];
     
         return $this->baseResponse(
             'Sukses Mendapatkan reminder.',
             'berhasil.',
             $resultReminder,
             200
         );
     }
    

}

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

use DB;
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

            // Return sukses
            return $this->baseResponse(
                'Sukses membuat transaksi.',
                'Transaksi berhasil ditambahkan.',
                $transaksi,
                201 
            );

        } catch (\Exception $e) {
           
            DB::rollBack(); 

            return $this->baseResponse(
                'Gagal membuat transaksi.',
                $e->getMessage(),
                null,
                500 
            );
        }
    }


    public function createCategory(Request $request){
        $request->validate([
            'category' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $category = Category::create([
                'category'=>$request->category,
                'id_type'=>$request->id_type,
            ]);
            DB::commit();
            return $this->baseResponse(
                'Sukses mendaftarkan Category.',
                'berhasil ditambahkan.',
                $category,
                201 
            );
        } catch (\Exception $e) {
            DB::rollBack(); 
            return $this->baseResponse(
                'Gagal Tambahkan.',
                $e->getMessage(),
                null,
                500 
            );
        }
    }

    public function getCategory(Request $request){
        $category = Category::get();

        if ($category->isEmpty()) {
            return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
        }

        return $this->baseResponse(
            'Sukses Mendapatkan Category.',
            'berhasil ditambahkan.',
            $category,
            200 
        );
    }

    
   

    public function createType(Request $request){
        $request->validate([
            'type' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $type = Type::create([
                'type'=>$request->type,
            ]);
            DB::commit();
            return $this->baseResponse(
                'Sukses mendaftarkan Type.',
                'berhasil ditambahkan.',
                $type,
                201 
            );
        } catch (\Exception $e) {
            DB::rollBack(); 
            return $this->baseResponse(
                'Gagal Tambahkan.',
                $e->getMessage(),
                null,
                500 
            );
        }
    }
    public function getType(Request $request){
        $type = Type::get();

        if ($type->isEmpty()) {
            return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
        }

        return $this->baseResponse(
            'Sukses Mendapatkan Type.',
            'berhasil ditambahkan.',
            $type,
            200 
        );
    }

    public function getMonthly(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',   
            'month' => 'required|integer|between:1,12', 
            'year' => 'required|integer',
        ]);
        
        if ($validator->fails()) {
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

        return $this->baseResponse(
            'Transaksi ditemukan.',
            'Data transaksi untuk user ID ' . $request->user_id . ' pada bulan ' . $month . ' tahun ' . $year,
            $dataResult,
            200
        );
    }

    
    public function createBudget(Request $request){
        $validator = \Validator::make($request->all(), [
            'id_category' => 'required', 
        ]);
        
        if ($validator->fails()) {
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
                'id_category'=>$request->id_category,
                'amount'=>$request->amount,
                'id_user'=>$request->id_user,
            ]);
            $transactionResponse = $this->createTransaction($request);
            if ($transactionResponse->status() !== 201) {
                DB::rollBack(); 
                return $transactionResponse;
            }
            DB::commit();
            return $this->baseResponse(
                'Sukses membuat transaksi.',
                'Anggaran berhasil diperbarui.',
                $budgets,
                201 
            );
        } catch (\Exception $e) {
            return $this->baseResponse(
                'Gagal.',
                $e->getMessage(),
                null,
                500 
            );
            DB::rollBack(); 
        }
      
    }

    public function refundBudget(Request $request){
        DB::beginTransaction();
        try {
          $refund = Budget::find($request->id_budget);
          $refund->status = false;
          $refund->save();
          $category_id = Category::find($request->id_category);
          $checktype = Type::find($category_id->id_type);
          
          $trans =  $request->merge([
            'id_user' => $request->id_user,
            'id_category' => $request->id_category,
            'id_type' => $checktype->id,
            'amount' => $refund->amount,
            'description' => 'Refund Budget Sebesar'.' '.$refund->amount,
            'date' => Carbon::now()->format('Y-m-d'),
            ]); 
          $transactionResponse = $this->createTransaction($trans);
            if ($transactionResponse->status() !== 201) {
                DB::rollBack(); 
                return $transactionResponse;
            }
          DB::commit();
          return $this->baseResponse(
              'Sukses membuat transaksi.',
              'Anggaran berhasil diperbarui.',
              $refund,
              201 
          );
        } catch (\Exception $e) {
            return $this->baseResponse(
                'Gagal.',
                $e->getMessage().$e->getLine(),
                null,
                500 
            );
            DB::rollBack(); 
        }
    }
    public function createReminders(Request $request)
    {
    
        $validator = \Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id',   
            'title' => 'required|string|max:255',    
            'amount' => 'required|numeric|min:1',    
            'due_date' => 'required|date', 
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return $this->baseResponse(
                'Input tidak lengkap atau tidak valid.',
                $validator->errors(),
                null,
                400 
            );
        }

        // Proses penyimpanan data
        DB::beginTransaction();
        try {
            $reminder = Reminder::create([
                'id_user' => $request->id_user,
                'title' => $request->title,
                'amount' => $request->amount,
                'due_date' => $request->due_date,
            ]);
            DB::commit(); 

            // Respon sukses
            return $this->baseResponse(
                'Sukses membuat pengingat.',
                'Reminder berhasil dibuat.',
                $reminder,
                201
            );
        } catch (\Exception $e) {
            DB::rollBack(); 
            return $this->baseResponse(
                'Gagal membuat pengingat.',
                $e->getMessage(),
                null,
                500 
            );
        }
    }

    public function payReminders(Request $request){
        DB::beginTransaction();
        try {
          $payReminder = Reminder::find($request->id_reminder);
          $payReminder->status = true;
          $payReminder->save();
          $category_id = Category::find($request->id_category);
          $checktype = Type::find($category_id->id_type);
          
          $trans =  $request->merge([
            'id_user' => $payReminder->id_user,
            'id_category' => $request->id_category,
            'id_type' => $checktype->id,
            'amount' => $payReminder->amount,
            'description' => $payReminder->title . ' '. $payReminder->due_date,
            'date' => Carbon::now()->format('Y-m-d'),
            ]); 
          $transactionResponse = $this->createTransaction($trans);
            if ($transactionResponse->status() !== 201) {
                DB::rollBack(); 
                return $transactionResponse;
            }
          DB::commit();
          return $this->baseResponse(
              'Sukses membuat transaksi.',
              'Anggaran berhasil diperbarui.',
              $payReminder,
              201 
          );
        } catch (\Exception $e) {
            return $this->baseResponse(
                'Gagal.',
                $e->getMessage().$e->getLine(),
                null,
                500 
            );
            DB::rollBack(); 
        }
    }

    public function getBudget(Request $request){
        $budgets = Budget::where('id_user', $request->id_user)
        ->leftJoin('categories', 'budgets.id_category', '=', 'categories.id')
        ->leftJoin('users', 'budgets.id_user', '=', 'users.id')
        ->select(
            'budgets.*',
            'categories.id as id_category',
            'categories.category as category_name',
            'users.name as user_name',
        )
        ->get();
        if ($budgets->isEmpty()) {
            return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
        }

        return $this->baseResponse(
            'Sukses Mendapatkan budgets.',
            'berhasil.',
            $budgets,
            200 
        );
    }

    public function GetReminder(Request $request){
        $reminder = Reminder::where('id_user', $request->id_user)
        ->leftJoin('users', 'reminders.id_user', '=', 'users.id')
        ->select(
            'reminders.*',
            'users.name as user_name',
        )
        ->get();
        if ($reminder->isEmpty()) {
            return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
        }

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
        $reminder = Reminder::where('id_user', $request->id_user)
            ->where('due_date', $today) // Hanya tanggal hari ini
            ->where('status', false) // Status harus false
            ->leftJoin('users', 'reminders.id_user', '=', 'users.id')
            ->select(
                'reminders.*',
                'users.name as user_name'
            )
            ->get();
    
        if ($reminder->isEmpty()) {
            return $this->baseResponse('Data Tidak Ditemukan', 'null', [], 404);
        }
    
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

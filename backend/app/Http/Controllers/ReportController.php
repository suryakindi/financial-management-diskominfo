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

class ReportController extends Controller
{
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
}

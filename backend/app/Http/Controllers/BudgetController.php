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
class BudgetController extends Controller
{
    

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

  
}

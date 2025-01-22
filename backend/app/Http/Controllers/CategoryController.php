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

class CategoryController extends Controller
{
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
}

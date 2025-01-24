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

class ReminderController extends Controller
{
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

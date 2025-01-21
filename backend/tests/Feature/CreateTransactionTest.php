<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Type;
use App\Models\Transactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Membuat User dengan saldo awal
        $this->user = User::create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password123'),
            'balance' => 1000, // Saldo Awal
        ]);

        // Membuat Type secara manual
        $this->incomeType = Type::create(['id' => 3, 'type' => 'Income']);
        $this->expenseType = Type::create(['id' => 1, 'type' => 'Expense']);
    

        // Membuat Kategori untuk setiap tipe
        $this->incomeCategory = Category::create([
            'id_type' => $this->incomeType->id,
            'category' => 'Penghasilan Freelance'
        ]);

        $this->expenseCategory = Category::create([
            'id_type' => $this->expenseType->id,
            'category' => 'Belanja'
        ]);
    }

    /** @test */
    public function test_seharusnya_membuat_transaksi_pendapatan_dan_memperbarui_saldo_user()
    {
        // Ambil token untuk user yang sudah terautentikasi
        $token = $this->user->createToken('TestToken')->plainTextToken;

        // Payload untuk transaksi
        $payload = [
            'id_user' => $this->user->id,
            'id_category' => $this->incomeCategory->id,
            'amount' => 500,
            'description' => 'Pendapatan dari freelance',
        ];
       
        // Kirim request POST dengan header Authorization: Bearer {token}
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/v1/transactions', $payload);

        // Assert response status dan pesan
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Sukses membuat transaksi.',
                     'status' => 'success',
                 ]);

        // Pastikan saldo user bertambah
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'balance' => 1500,
        ]);

        // Pastikan transaksi tercatat
        $this->assertDatabaseHas('transactions', [
            'id_user' => $this->user->id,
            'id_category' => $this->incomeCategory->id,
            'amount' => 500,
        ]);
    }

    /** @test */
    public function test_seharusnya_membuat_transaksi_pengeluaran_dan_memperbarui_saldo_user()
    {
        // Ambil token untuk user yang sudah terautentikasi
        $token = $this->user->createToken('TestToken')->plainTextToken;

        // Payload untuk transaksi
        $payload = [
            'id_user' => $this->user->id,
            'id_category' => $this->expenseCategory->id,
            'amount' => 500,
            'description' => 'Belanja kebutuhan',
        ];

        // Kirim request POST dengan header Authorization: Bearer {token}
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/transactions', $payload);

        // Assert response status dan pesan
        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Sukses membuat transaksi.',
                    'status' => 'success',
                ]);

        // Pastikan saldo user berkurang
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'balance' => 500, // 1000 awal - 500
        ]);

        // Pastikan transaksi tercatat
        $this->assertDatabaseHas('transactions', [
            'id_user' => $this->user->id,
            'id_category' => $this->expenseCategory->id,
            'amount' => 500,
        ]);
    }

    /** @test */
    public function test_seharusnya_gagal_ketika_saldo_tidak_cukup()
    {
        // Ambil token untuk user yang sudah terautentikasi
        $token = $this->user->createToken('TestToken')->plainTextToken;

        // Payload untuk transaksi
        $payload = [
            'id_user' => $this->user->id,
            'id_category' => $this->expenseCategory->id,
            'amount' => 1500, // Lebih dari saldo
            'description' => 'Pengeluaran melebihi saldo',
        ];

        // Kirim request POST dengan header Authorization: Bearer {token}
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/transactions', $payload);

        // Assert response status dan pesan error
        $response->assertStatus(400)
                ->assertJson([
                    'message' => 'Gagal membuat transaksi.',
                    'status' => 'error',
                ]);

        // Pastikan saldo user tetap
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'balance' => 1000, // Tidak berubah
        ]);

        // Tidak ada transaksi tercatat
        $this->assertDatabaseMissing('transactions', [
            'id_user' => $this->user->id,
            'amount' => 1500,
        ]);
    }

    /** @test */
    public function test_seharusnya_gagal_ketika_input_invalid()
    {
        // Ambil token untuk user yang sudah terautentikasi
        $token = $this->user->createToken('TestToken')->plainTextToken;

        // Payload untuk transaksi yang invalid
        $payload = [
            'id_user' => '', // Kosong
            'id_category' => '',
            'amount' => -100, 
            'description' => '',
        ];

        // Kirim request POST dengan header Authorization: Bearer {token}
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/transactions', $payload);

        // Assert response status dan pesan error
        $response->assertStatus(400)
                ->assertJson([
                    'message' => 'Input tidak lengkap atau tidak valid.',
                    'status' => 'error',
                ]);

        // Tidak ada transaksi tercatat
        $this->assertDatabaseMissing('transactions', [
            'amount' => -100,
        ]);
    }
}

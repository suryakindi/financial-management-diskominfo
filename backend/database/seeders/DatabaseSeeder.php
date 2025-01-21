<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Type;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */

    public function run()
    {
        Category::truncate();  // Menghapus semua data di kategori
        Type::truncate();
        $category = [
            ['category' => 'food', 'id_type'=> '1'],
            ['category' => 'fashion', 'id_type'=> '1'],
            ['category' => 'save', 'id_type'=> '3'],
            ['id'=>99, 'category' => 'reminder', 'id_type'=> '1'],
           
        ];
        foreach ($category as $item) {
            Category::create($item);
        }

        $type = [
            ['id'=> 3, 'type' => 'income'],
            ['type' => 'expense'],
           
        ];
        foreach ($type as $item) {
            Type::create($item);
        }
    }
}

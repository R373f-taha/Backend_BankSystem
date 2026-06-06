<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // توليد 50 عملية بنكية وهمية وموزعة عشوائياً على العملاء
        Transaction::factory(50)->create();
    }
}
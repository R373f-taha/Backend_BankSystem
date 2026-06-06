<?php

namespace Database\Seeders;

use App\Models\Faqs;
use Illuminate\Database\Seeder;

class FaqsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Faqs::factory(5)->create();
    }
}
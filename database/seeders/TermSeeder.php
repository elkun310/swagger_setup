<?php

namespace Database\Seeders;

use App\Models\Term;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tạo 50 bản ghi ngẫu nhiên cho bảng terms
        Term::factory()->count(50)->create();
    }
}

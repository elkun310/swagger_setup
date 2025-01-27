<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin@123'),  // Sử dụng hàm Hash::make để mã hóa mật khẩu
        ]);

        // Bạn có thể tạo thêm nhiều người dùng mẫu khác nếu cần
        User::factory(10)->create();  // Sử dụng factory để tạo thêm 10 người dùng
    }
}

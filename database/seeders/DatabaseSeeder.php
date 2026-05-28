<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // 1. สร้างพนักงานขายเริ่มต้น (เอาไว้ล็อกอินทดสอบระบบ)
        \App\Models\User::factory()->create([
            'name' => 'Sales Team A',
            'email' => 'sales@company.com',
            'password' => bcrypt('12345678'), // รหัสผ่านสำหรับเข้าเว็บ
            'role' => 'sales',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Manager Admin',
            'email' => 'admin@company.com',
            'password' => bcrypt('12345678'), // รหัสผ่านสำหรับเข้าเว็บ
            'role' => 'admin',
        ]);

        // 2. ใส่รายชื่อคอร์สทั้ง 14 คอร์สจากไฟล์ Excel ของคุณ
        $courses = [
            'ALT', 'IOT', 'Drone', 'Software custom', 'E-driving', 
            'TSM', 'ADR', 'รถจักรยานยนต์', 'รถยนต์', 'รถบรรทุก', 
            'อบรมขับขี่ปลอดภัย', 'บินโดรน', 'จป.', 'EVOC'
        ];

        foreach ($courses as $courseName) {
            \App\Models\Course::create([
                'course_name' => $courseName
            ]);
        }
    }
}
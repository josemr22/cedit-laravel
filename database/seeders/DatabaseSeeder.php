<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Extra;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            BankSeeder::class,
            CourseSeeder::class,
            TurnSeeder::class,
            CourseTurnSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            StudentSeeder::class,
            SpendingSeeder::class,
            CorrelativeSeeder::class
        ]);

        Extra::create([]);
    }
}

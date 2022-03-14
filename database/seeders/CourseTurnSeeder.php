<?php

namespace Database\Seeders;

use App\Models\CourseTurn;
use Illuminate\Database\Seeder;

class CourseTurnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        for ($i = 1; $i < 5; $i++) {
            CourseTurn::create([
                "course_id" => $i,
                "turn_id" => 1,
                "days" => "Lunes",
                "start_hour" => "19:00:00",
                "end_hour" => "20:00:00"
            ]);
        }
    }
}

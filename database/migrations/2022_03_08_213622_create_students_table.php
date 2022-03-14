<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('dni');
            $table->string('email');
            $table->foreignId('department_id');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('cellphone');
            $table->string('observation')->nullable();
            $table->foreignId('course_id');
            $table->foreignId('course_turn_id')->nullable();
            $table->boolean('signed_up')->default(0);
            $table->foreignId('registered_by')->nullable();
            $table->string('registered_at')->nullable();
            $table->foreignId('enrolled_by')->nullable();
            $table->string('enrolled_at')->nullable();
            $table->string('start_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}

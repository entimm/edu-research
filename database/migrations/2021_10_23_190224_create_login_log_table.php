<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school')->default('');
            $table->string('class')->default('');
            $table->string('name')->default('');
            $table->string('grade')->default('');
            $table->string('age')->default('');
            $table->string('sex')->default('');
            $table->string('student_no')->default('');
            $table->string('user_agent')->default('');
            $table->string('ip')->default('');
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
        Schema::dropIfExists('login_log');
    }
}

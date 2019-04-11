<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dummy_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dummy_header_id')->index();
            $table->string('kolom_string');
            $table->string('kolom_select')->nullable();
            $table->integer('kolom_currency')->nullable();
            $table->dateTime('kolom_date')->nullable();
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
        Schema::drop('dummy_lines');
    }
}

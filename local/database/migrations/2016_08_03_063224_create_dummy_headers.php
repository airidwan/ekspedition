<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyHeaders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dummy_headers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kolom_string');
            $table->string('kolom_select')->nullable();
            $table->string('kolom_autocomplete')->nullable();
            $table->integer('kolom_currency')->nullable();
            $table->string('kolom_textarea')->nullable();
            $table->dateTime('kolom_date');
            $table->string('kolom_checkbox')->nullable();
            $table->string('kolom_radio')->nullable();
            $table->string('kolom_foto')->nullable();
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
        Schema::drop('dummy_headers');
    }
}

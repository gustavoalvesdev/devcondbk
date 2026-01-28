<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitvehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unitvehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_unit');
            $table->foreign('id_unit')->references('id')->on('units')->onDelete('cascade');
            $table->string('title');
            $table->string('color');
            $table->string('plate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unitvehicles');
    }
}

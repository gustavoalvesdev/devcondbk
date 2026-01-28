<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitpetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unitpets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_unit');
            $table->foreign('id_unit')->references('id')->on('units')->onDelete('cascade');
            $table->string('name');
            $table->string('race');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unitpets');
    }
}

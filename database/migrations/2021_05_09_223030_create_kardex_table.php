<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKardexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kardex', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('date', 30);
            $table->string('quantity', 10);
            $table->string('previousStock', 10);
            $table->string('currentStock', 10);
            $table->string('voucher', 15);

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('area_assignment_id');
            $table->foreign('area_assignment_id')->references('id')->on('area_assignments');

            $table->unsignedBigInteger('movement_id');
            $table->foreign('movement_id')->references('id')->on('movements');

            $table->unsignedBigInteger('entity_id');
            $table->foreign('entity_id')->references('id')->on('entities');

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
        Schema::dropIfExists('kardex');
    }
}

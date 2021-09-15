<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('barcode', 60)->unique()->nullable();
            $table->string('name', 60);
            $table->string('short_name', 15)->nullable();
            $table->bigInteger('maximum_stock');
            $table->bigInteger('minimum_stock');
            $table->bigInteger('box_quantity');
            $table->bigInteger('blister_quantity');
            $table->decimal('presentation_sale');
            $table->decimal('buy_unit' ,8, 4);
            $table->decimal('buy_blister', 8, 4);
            $table->decimal('buy_box');
            $table->decimal('sale_unit');
            $table->decimal('sale_blister');
            $table->decimal('sale_box');
            $table->decimal('minimum_sale_unit')->nullable();
            $table->decimal('minimum_sale_blister')->nullable();
            $table->decimal('minimum_sale_box')->nullable();
            $table->boolean('control_expiration');
            $table->boolean('control_stock');
            $table->boolean('control_refrigeration');
            $table->boolean('control_prescription');
            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->unsignedBigInteger('lab_mark_id')->nullable();
            $table->foreign('lab_mark_id')->references('id')->on('lab_marks');

            $table->unsignedBigInteger('active_principle_id')->nullable();
            $table->foreign('active_principle_id')->references('id')->on('active_principles');

            $table->unsignedBigInteger('therapeutic_action_id')->nullable();
            $table->foreign('therapeutic_action_id')->references('id')->on('therapeutic_actions');

            $table->unsignedBigInteger('presentation_id')->nullable();
            $table->foreign('presentation_id')->references('id')->on('presentations');

            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('locations');

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
        Schema::dropIfExists('products');
    }
}

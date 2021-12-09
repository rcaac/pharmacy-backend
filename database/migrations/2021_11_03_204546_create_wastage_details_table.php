<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWastageDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wastage_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('quantity');
            $table->decimal('cost_unit' ,10, 4);
            $table->decimal('cost_total', 10, 4);
            $table->string('lot', 30)->nullable();
            $table->timestamp('date_expiration');
            $table->string('condition', 2);

            $table->unsignedBigInteger('entity_id');
            $table->foreign('entity_id')->references('id')->on('entities');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('wastage_id');
            $table->foreign('wastage_id')->references('id')->on('wastages');

            $table->unsignedBigInteger('detail_invoice_purchase_id');
            $table->foreign('detail_invoice_purchase_id')->references('id')->on('detail_invoice_purchases');

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
        Schema::dropIfExists('wastage_details');
    }
}

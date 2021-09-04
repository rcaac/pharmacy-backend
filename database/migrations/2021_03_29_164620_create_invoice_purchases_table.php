<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number', 15);
            $table->string('date', 30);
            $table->decimal('subtotal');
            $table->decimal('total');
            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');

            $table->unsignedBigInteger('state_invoice_purchase_id');
            $table->foreign('state_invoice_purchase_id')->references('id')->on('state_invoice_purchases');

            $table->unsignedBigInteger('type_invoice_purchase_id');
            $table->foreign('type_invoice_purchase_id')->references('id')->on('type_invoice_purchases');

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
        Schema::dropIfExists('invoice_purchases');
    }
}

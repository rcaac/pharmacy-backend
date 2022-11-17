<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailTicketInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_ticket_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lot', 30)->nullable();
            $table->string('expiration_date', 30)->nullable();
            $table->string('quantity', 10);
            $table->string('sale_unit', 10);
            $table->string('sale_blister', 10);
            $table->string('sale_box', 10);
            $table->string('total', 10);
            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->unsignedBigInteger('ticket_invoice_id');
            $table->foreign('ticket_invoice_id')->references('id')->on('ticket_invoices');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

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
        Schema::dropIfExists('detail_ticket_invoices');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('date', 20);
            $table->string('subtotal', 11);
            $table->string('total', 11);
            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->unsignedBigInteger('type_ticket_invoice_id');
            $table->foreign('type_ticket_invoice_id')->references('id')->on('type_ticket_invoices');

            $table->unsignedBigInteger('state_ticket_invoice_id');
            $table->foreign('state_ticket_invoice_id')->references('id')->on('state_ticket_invoices');

            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedBigInteger('cash_id')->nullable();
            $table->foreign('cash_id')->references('id')->on('cashes');

            $table->unsignedBigInteger('type_buy_id')->nullable();
            $table->foreign('type_buy_id')->references('id')->on('type_buys');

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
        Schema::dropIfExists('ticket_invoices');
    }
}

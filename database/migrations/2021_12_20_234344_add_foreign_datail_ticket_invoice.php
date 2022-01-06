<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignDatailTicketInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detail_ticket_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_invoice_purchase_id')->after('product_id')->nullable();
            $table->foreign('detail_invoice_purchase_id')->references('id')->on('detail_invoice_purchases');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detail_ticket_invoices', function (Blueprint $table) {
            $table->dropColumn('detail_invoice_purchase_id');
        });
    }
}

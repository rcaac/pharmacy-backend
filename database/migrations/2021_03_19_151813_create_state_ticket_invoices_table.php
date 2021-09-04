<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStateTicketInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state_ticket_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->timestamps();
        });

        DB::table('state_ticket_invoices')->insert(array('id'=>'1','name'=>'EMITIDO','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('state_ticket_invoices')->insert(array('id'=>'2','name'=>'ANULADO','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('state_ticket_invoices');
    }
}

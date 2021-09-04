<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTypeTicketInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_ticket_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->timestamps();
        });
        DB::table('type_ticket_invoices')->insert(array('id'=>'1','name'=>'FACTURA','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('type_ticket_invoices')->insert(array('id'=>'2','name'=>'BOLETA','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('type_ticket_invoices')->insert(array('id'=>'3','name'=>'TICKET','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('type_ticket_invoices')->insert(array('id'=>'4','name'=>'GUIA','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('type_ticket_invoices');
    }
}

<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStateInvoicePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state_invoice_purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description', 30);
            $table->timestamps();
        });
        DB::table('state_invoice_purchases')->insert(array('id'=>'1','description'=>'EMITIDO','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('state_invoice_purchases')->insert(array('id'=>'2','description'=>'ANULADO','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('state_invoice_purchases');
    }
}

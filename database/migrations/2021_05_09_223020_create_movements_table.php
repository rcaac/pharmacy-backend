<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->timestamps();
        });
        DB::table('movements')->insert(array('id'=>'1','name'=>'COMPRA', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('movements')->insert(array('id'=>'2','name'=>'VENTA', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('movements')->insert(array('id'=>'3','name'=>'ANULACION COMPRA', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('movements')->insert(array('id'=>'4','name'=>'ANULACION VENTA', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('movements')->insert(array('id'=>'5','name'=>'MERMA', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movements');
    }
}

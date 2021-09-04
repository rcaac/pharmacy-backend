<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTypeBuysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_buys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 20);
            $table->timestamps();
        });
        DB::table('type_buys')->insert(array('id'=>'1','name'=>'EFECTIVO','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('type_buys')->insert(array('id'=>'2','name'=>'TARJETA','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('type_buys')->insert(array('id'=>'3','name'=>'VALES','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('type_buys');
    }
}

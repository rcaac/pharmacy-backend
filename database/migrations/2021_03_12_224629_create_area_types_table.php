<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAreaTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->timestamps();
        });
        DB::table('area_types')->insert(array('id'=>'1','name'=>'ADMINISTRATIVA','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('area_types')->insert(array('id'=>'2','name'=>'VENTAS','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('area_types')->insert(array('id'=>'3','name'=>'ALMACEN','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area_types');
    }
}

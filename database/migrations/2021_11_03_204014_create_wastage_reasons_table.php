<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWastageReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wastage_reasons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 60);
            $table->timestamps();
        });

        DB::table('wastage_reasons')->insert(array('id'=>'1','name'=>'VENCIDO', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('wastage_reasons')->insert(array('id'=>'2','name'=>'DETERIORO', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('wastage_reasons')->insert(array('id'=>'3','name'=>'USO PERSONAL', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('wastage_reasons')->insert(array('id'=>'4','name'=>'TÓPICO BOTICA', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('wastage_reasons')->insert(array('id'=>'5','name'=>'INVENTARIO', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('wastage_reasons')->insert(array('id'=>'6','name'=>'EXCESO', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('wastage_reasons')->insert(array('id'=>'7','name'=>'TRANSFERENCIA', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wastage_reasons');
    }
}

<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEntityTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->timestamps();
        });
        DB::table('entity_types')->insert(array('id'=>'1','name'=>'BOTICA','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('entity_types')->insert(array('id'=>'2','name'=>'FARMACIA','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('entity_types')->insert(array('id'=>'3','name'=>'DROGUERIA','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_types');
    }
}

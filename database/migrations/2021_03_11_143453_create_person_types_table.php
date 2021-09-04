<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePersonTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->timestamps();
        });

        DB::table('person_types')->insert(array('id'=>'1','name'=>'TIPO 1','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('person_types')->insert(array('id'=>'2','name'=>'TIPO 2','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('person_types')->insert(array('id'=>'3','name'=>'TIPO 3','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_types');
    }
}

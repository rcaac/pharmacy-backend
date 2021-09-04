<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignment_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 20);
            $table->timestamps();
        });

        DB::table('assignment_states')->insert(array('id'=>'1','name'=>'ACTIVO','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('assignment_states')->insert(array('id'=>'2','name'=>'INACTIVO','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
        DB::table('assignment_states')->insert(array('id'=>'3','name'=>'SUSPENDIDO','created_at'=>Carbon::now(),'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignment_states');
    }
}

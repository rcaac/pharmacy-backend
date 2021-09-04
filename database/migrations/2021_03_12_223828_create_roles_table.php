<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->string('description', 60);
            $table->timestamps();
        });
        DB::table('roles')->insert(array('id'=>'1','name'=>'admin', 'description'=>'ADMINISTRADOR', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('roles')->insert(array('id'=>'2','name'=>'contador', 'description'=>'CONTADOR', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
        DB::table('roles')->insert(array('id'=>'3','name'=>'cajero', 'description'=>'CAJERO', 'created_at'=>Carbon::now(), 'updated_at'=>Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}

<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 10);
            $table->string('password');
            $table->string('created_by', 5);
            $table->string('condition', 2);
            $table->timestamps();

            $table->unsignedBigInteger('person_id');
            $table->foreign('person_id')->references('id')->on('persons');
        });
        DB::table('users')->insert(array(
            'id'=>'1',
            'name'=>'12345678',
            'password'=>'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'created_by' => '1',
            'condition' => '1',
            'person_id' => 1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstName', 60)->nullable();
            $table->string('lastName', 60)->nullable();
            $table->string('dni', 8)->unique()->nullable();
            $table->string('ruc', 15)->unique()->nullable();
            $table->string('direction', 100)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email', 60)->unique()->nullable();
            $table->string('businessName')->nullable();
            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->unsignedBigInteger('person_type_id');
            $table->foreign('person_type_id')->references('id')->on('person_types');

            $table->timestamps();
        });
        DB::table('persons')->insert(array(
            'id'                         => '1',
            'firstName'                  => 'César Andrés',
            'lastName'                   => 'Atachagua Contreras',
            'dni'                        => '12345678',
            'ruc'                        => '1234567890',
            'direction'                  => 'Jr. Junin 155',
            'phone'                      => '12457889',
            'email'                      => 'admin@gmail.com',
            'created_by'                 => '1',
            'condition'                  => '1',
            'person_type_id'             => '1',
            'created_at'                 => Carbon::now(),
            'updated_at'                 => Carbon::now()
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('persons');
    }
}

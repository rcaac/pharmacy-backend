<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAreaAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles');

            $table->unsignedBigInteger('area_id');
            $table->foreign('area_id')->references('id')->on('areas');

            $table->unsignedBigInteger('person_id');
            $table->foreign('person_id')->references('id')->on('persons');

            $table->unsignedBigInteger('assignment_state_id');
            $table->foreign('assignment_state_id')->references('id')->on('assignment_states');

            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->timestamps();
        });

        DB::table('area_assignments')->insert(array(
            'id'                         => '1',
            'role_id'                    => '1',
            'area_id'                    => '1',
            'created_by'                 => '1',
            'condition'                  => '1',
            'person_id'                  => '1',
            'assignment_state_id'        => '1',
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
        Schema::dropIfExists('area_assignments');
    }
}

<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 60);
            $table->string('direction', 80)->nullable();
            $table->string('ruc', 11)->unique()->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->unsignedBigInteger('entity_type_id');
            $table->foreign('entity_type_id')->references('id')->on('entity_types');

            $table->foreign('parent_id')->references('id')->on('entities');

            $table->timestamps();
        });

        DB::table('entities')->insert(array(
            'id'                         => '1',
            'name'                       => 'BUSINESS++',
            'direction'                  => 'Pillcomarca',
            'ruc'                        => '23568978451',
            'parent_id'                  => null,
            'created_by'                 => '1',
            'condition'                  => '1',
            'entity_type_id'             => '2',
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
        Schema::dropIfExists('entities');
    }
}

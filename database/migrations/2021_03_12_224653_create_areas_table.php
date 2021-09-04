<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->unsignedBigInteger('parent_id')->nullable();;
            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->unsignedBigInteger('area_type_id');
            $table->foreign('area_type_id')->references('id')->on('area_types');

            $table->unsignedBigInteger('entity_id');
            $table->foreign('entity_id')->references('id')->on('entities');

            $table->foreign('parent_id')->references('id')->on('areas');

            $table->timestamps();
        });

        DB::table('areas')->insert(array(
            'id'                         => '1',
            'name'                       => 'DESARROLLO',
            'parent_id'                  => NULL,
            'created_by'                 => '1',
            'condition'                  => '1',
            'area_type_id'               => '1',
            'entity_id'                  => '1',
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
        Schema::dropIfExists('areas');
    }
}

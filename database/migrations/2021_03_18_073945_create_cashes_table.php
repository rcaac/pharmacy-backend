<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('opening_date', 20)->nullable();
            $table->string('closing_date', 20)->nullable();
            $table->decimal('initial_balance')->nullable();
            $table->decimal('final_balance')->nullable();
            $table->boolean('state');
            $table->string('created_by', 5);
            $table->string('condition', 2);
            $table->string('observations')->nullable();

            $table->unsignedBigInteger('area_assignment_id')->nullable();
            $table->foreign('area_assignment_id')->references('id')->on('area_assignments');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cashes');
    }
}

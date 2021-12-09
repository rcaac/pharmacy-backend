<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('date');
            $table->timestamp('date_expiration');
            $table->bigInteger('quantity');
            $table->string('lot', 30);
            $table->string('created_by', 5);
            $table->string('condition', 2);

            $table->unsignedBigInteger('entity');
            $table->foreign('entity')->references('id')->on('entities');

            $table->unsignedBigInteger('entity_responsible');
            $table->foreign('entity_responsible')->references('id')->on('entities');

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
        Schema::dropIfExists('transfers');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNumberTable extends Migration
{
    public function up()
    {
        Schema::table('ticket_invoices', function (Blueprint $table) {
            $table->string('prefijo')->after('id')->nullable();
            $table->string('numero')->after('prefijo')->nullable();
            $table->string('igv',2)->after('subtotal')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_invoices', function (Blueprint $table) {
            $table->dropColumn('prefijo');
            $table->dropColumn('numero');
            $table->dropColumn('igv');
        });

    }


}

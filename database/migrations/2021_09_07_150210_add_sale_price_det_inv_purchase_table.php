<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalePriceDetInvPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detail_invoice_purchases', function (Blueprint $table) {
            $table->decimal('sale_blister',8,2)->after('sale_unit')->nullable();
            $table->decimal('sale_box',8,2)->after('sale_blister')->nullable();
            $table->decimal('minimum_sale_unit',8,2)->after('sale_box')->nullable();
            $table->decimal('minimum_sale_blister',8,2)->after('minimum_sale_unit')->nullable();
            $table->decimal('minimum_sale_box',8,2)->after('minimum_sale_blister')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detail_invoice_puchases', function (Blueprint $table) {
            $table->dropColumn('sale_blister');
            $table->dropColumn('sale_box');
            $table->dropColumn('minimum_sale_unit');
            $table->dropColumn('minimum_sale_blister');
            $table->dropColumn('minimum_sale_box');
        });
    }
}

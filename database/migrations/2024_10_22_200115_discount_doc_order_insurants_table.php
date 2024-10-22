<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DiscountDocOrderInsurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_insurants', function (Blueprint $table) {
            $table->string('discount_doc_number')->nullable();
            $table->string('discount_doc_series')->nullable();
            $table->date('discount_doc_date')->nullable();
            $table->string('discount_doc_given')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_insurants', function (Blueprint $table) {
            $table->dropColumn('discount_doc_number');
            $table->dropColumn('discount_doc_series');
            $table->dropColumn('discount_doc_date');
            $table->dropColumn('discount_doc_given');
        });
    }
}

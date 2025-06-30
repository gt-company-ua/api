<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdditionalDataOrderTransportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_transports', function (Blueprint $table) {
            $table->integer('engine_capacity')->unsigned()->nullable();
            $table->integer('total_weight')->unsigned()->nullable();
            $table->integer('own_weight')->unsigned()->nullable();
            $table->smallInteger('seats_count')->unsigned()->nullable();
            $table->integer('odometer')->unsigned()->nullable();
            $table->smallInteger('e_power')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_transports', function (Blueprint $table) {
            $table->dropColumn('engine_capacity');
            $table->dropColumn('total_weight');
            $table->dropColumn('own_weight');
            $table->dropColumn('seats_count');
            $table->dropColumn('e_power');
            $table->dropColumn('odometer');
        });
    }
}

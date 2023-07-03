<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoalOrderTouristsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_tourists', function (Blueprint $table) {
            $table->string('goal', 16)->nullable();
            $table->string('doc_series')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_tourists', function (Blueprint $table) {
            $table->dropColumn('goal');
            $table->dropColumn('doc_series');
        });
    }
}

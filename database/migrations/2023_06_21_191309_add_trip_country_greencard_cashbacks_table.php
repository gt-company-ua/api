<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTripCountryGreencardCashbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('greencard_cashbacks', function (Blueprint $table) {
            $table->string('trip_country', 32)->default(\App\Models\Order::TRIP_COUNTRY_EU);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('greencard_cashbacks', function (Blueprint $table) {
            $table->dropColumn('trip_country');
        });
    }
}

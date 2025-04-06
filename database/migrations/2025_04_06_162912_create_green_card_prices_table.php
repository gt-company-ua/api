<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGreenCardPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('green_card_prices', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('months')->unsigned();
            $table->decimal('amount')->nullable();
            $table->string('trip_country', 32)->default(\App\Models\Order::TRIP_COUNTRY_EU);
            $table->string('transport_type', 64)->nullable()->default('default');
            $table->string('insurance_company')->default(\App\Services\api\Ingo::API_NAME)->nullable();
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
        Schema::dropIfExists('green_card_prices');
    }
}

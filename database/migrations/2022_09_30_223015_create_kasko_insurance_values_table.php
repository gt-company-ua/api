<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaskoInsuranceValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kasko_insurance_values', function (Blueprint $table) {
            $table->id();
            $table->integer('price_from')->unsigned()->nullable();
            $table->integer('price_to')->unsigned()->nullable();
            $table->boolean('is_truck')->default(false);
            $table->decimal('coefficient', 5, 2);
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
        Schema::dropIfExists('kasko_insurance_values');
    }
}

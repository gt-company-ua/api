<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOsagoTariffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('osago_tariffs', function (Blueprint $table) {
            $table->id();
            $table->integer('franchise')->unsigned()->nullable();
            $table->string('tariff');
            $table->decimal('coefficient', 3, 2);
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
        Schema::dropIfExists('osago_tariffs');
    }
}

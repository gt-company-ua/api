<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssistMePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assist_me_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\TransportCategory::class)->constrained()->cascadeOnDelete();
            $table->smallInteger('trip_duration')->unsigned()->nullable();
            $table->decimal('price')->unsigned()->nullable();
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
        Schema::dropIfExists('assist_me_prices');
    }
}

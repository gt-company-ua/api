<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVignetteProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vignette_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Country::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Country::class)
                ->constrained()
                ->nullOnDelete();
            $table->string('name');
            $table->string('vehicle_type');
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
        Schema::dropIfExists('vignette_country_products');
    }
}

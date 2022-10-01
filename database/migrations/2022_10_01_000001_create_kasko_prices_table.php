<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaskoPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kasko_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\KaskoInsuranceValue::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->tinyInteger('years')->nullable();
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
        Schema::dropIfExists('kasko_prices');
    }
}

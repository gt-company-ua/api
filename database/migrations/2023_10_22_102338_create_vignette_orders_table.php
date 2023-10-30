<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVignetteOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vignette_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\VignetteProduct::class)
                ->constrained()
                ->restrictOnDelete();
            $table->string('external_id')->nullable();
            $table->string('currency')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->timestamp('start_date');
            $table->integer('period');
            $table->decimal('amount')->nullable();
            $table->decimal('amount_fee')->nullable();
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
        Schema::dropIfExists('vignette_orders');
    }
}

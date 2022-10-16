<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTransportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Order::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\TransportCategory::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignIdFor(\App\Models\TransportPower::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('car_mark')->nullable();
            $table->string('car_model')->nullable();
            $table->string('car_year')->nullable();
            $table->string('gov_num')->nullable();
            $table->string('vin')->nullable();
            $table->string('registration_type')->nullable();
            $table->date('otk_date')->nullable();
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
        Schema::dropIfExists('order_transports');
    }
}

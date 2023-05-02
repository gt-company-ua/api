<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderAssistMeContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_assist_me_contracts', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Order::class)
                ->primary()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('number', 100)->nullable();
            $table->decimal('price')->unsigned()->nullable();
            $table->string('payment_type')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('payment_id')->nullable();
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
        Schema::dropIfExists('order_assist_me_contracts');
    }
}

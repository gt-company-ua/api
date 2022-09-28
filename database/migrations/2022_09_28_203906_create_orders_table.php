<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36);

            $table->enum('order_type', \App\Models\Order::ORDER_TYPES);
            $table->date('polis_start')->nullable();
            $table->date('polis_end')->nullable();
            $table->tinyInteger('foreign_check')->unsigned()->nullable();
            $table->tinyInteger('discount_check')->unsigned()->nullable();
            $table->string('trip_country')->nullable();
            $table->tinyInteger('trip_duration')->unsigned()->nullable();

            $table->string('email')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->string('city_name')->nullable();

            $table->tinyInteger('upload_docs')->unsigned()->nullable();

            $table->decimal('price')->unsigned()->nullable();
            $table->tinyInteger('gc_plus')->unsigned()->nullable();
            $table->decimal('gc_plus_price')->unsigned()->nullable();

            $table->string('payment_type')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('payment_url_short')->nullable();

            $table->tinyInteger('dont_call')->unsigned()->nullable();
            $table->string('bonus')->nullable();

            $table->text('comment')->nullable();
            $table->string('ga_id', 100)->nullable();

            $table->string('send_sms', 12)->nullable();
            $table->string('contract_num', 100)->nullable();
            $table->string('contract_state', 32)->nullable();
            $table->text('contract_response')->nullable();

            $table->integer('crm_contact_id')->nullable();
            $table->integer('crm_deal_id')->nullable();
            $table->integer('crm_car_id')->nullable();

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
        Schema::dropIfExists('orders');
    }
}

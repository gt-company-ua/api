<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderInsurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_insurants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('type', Order::INSURANT_TYPES)->nullable();

            $table->string('phone')->nullable();
            $table->string('surname')->nullable();
            $table->string('name')->nullable();
            $table->string('patronymic')->nullable();
            $table->string('surname_latin')->nullable();
            $table->string('name_latin')->nullable();
            $table->date('birth')->nullable();

            $table->string('address')->nullable();
            $table->string('address_latin')->nullable();

            $table->string('inn')->nullable();
            $table->string('doc_type')->nullable();
            $table->string('doc_number')->nullable();
            $table->string('doc_series')->nullable();
            $table->date('doc_date')->nullable();
            $table->string('doc_given')->nullable();

            $table->string('enterprise_name')->nullable();
            $table->string('enterprise_address')->nullable();
            $table->string('enterprise_code')->nullable();

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
        Schema::dropIfExists('order_insurants');
    }
}

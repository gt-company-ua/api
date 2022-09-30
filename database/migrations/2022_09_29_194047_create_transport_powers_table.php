<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportPowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_powers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\TransportCategory::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name_ua');
            $table->string('name_ru');
            $table->string('api_id')->nullable();
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
        Schema::dropIfExists('transport_powers');
    }
}

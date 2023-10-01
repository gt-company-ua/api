<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsConfirmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_confirms', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('code')->nullable();
            $table->smallInteger('wrong_count')->nullable();
            $table->string('status')->nullable();
            $table->string('external_id')->nullable();
            $table->string('response_status')->nullable();
            $table->string('response_code')->nullable();
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
        Schema::dropIfExists('sms_confirms');
    }
}

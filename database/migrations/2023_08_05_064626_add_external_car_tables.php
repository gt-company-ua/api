<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalCarTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('car_marks', function (Blueprint $table) {
            $table->string('external_id')->nullable();
        });

        Schema::table('car_models', function (Blueprint $table) {
            $table->string('external_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('car_marks', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });

        Schema::table('car_models', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
    }
}

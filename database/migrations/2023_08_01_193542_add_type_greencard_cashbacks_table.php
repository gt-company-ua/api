<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeGreencardCashbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('greencard_cashbacks', function (Blueprint $table) {
            $table->string('transport_type', 64)->nullable()->default('default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('greencard_cashbacks', function (Blueprint $table) {
            $table->dropColumn('transport_type');
        });
    }
}

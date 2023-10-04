<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrderContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_contracts', function (Blueprint $table) {
            $table->boolean('sent_police')->default(false);
            $table->smallInteger('download_attempts')->nullable();
            $table->smallInteger('download_status_code')->unsigned()->nullable();
        });
        \Illuminate\Support\Facades\DB::update("UPDATE order_contracts SET sent_police = true WHERE state = 'Signed'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_contracts', function (Blueprint $table) {
            $table->dropColumn(['sent_police', 'download_attempts', 'download_status_code']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileLinkOrderContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_contracts', function (Blueprint $table) {
            $table->string('file_link', 1000)->nullable();
            $table->string('api_name', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_contracts', function (Blueprint $table) {
            $table->dropColumn('file_link');
            $table->dropColumn('api_name');
        });
    }
}

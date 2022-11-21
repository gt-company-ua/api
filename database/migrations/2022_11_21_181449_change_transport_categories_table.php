<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTransportCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_categories', function (Blueprint $table) {
            $table->boolean('show_osago')->default(true);
            $table->boolean('show_zk')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transport_categories', function (Blueprint $table) {
            $table->dropColumn('show_osago');
            $table->dropColumn('show_zk');
        });
    }
}

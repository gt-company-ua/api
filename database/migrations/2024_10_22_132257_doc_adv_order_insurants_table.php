<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DocAdvOrderInsurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_insurants', function (Blueprint $table) {
            $table->string('doc_adv')->after('doc_given')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_insurants', function (Blueprint $table) {
            $table->dropColumn('doc_adv');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsuranceCompanyOsagoCashbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('osago_cashbacks', function (Blueprint $table) {
            $table->string('insurance_company')->default(\App\Services\api\Ingo::API_NAME)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('osago_cashbacks', function (Blueprint $table) {
            $table->dropColumn('insurance_company');
        });
    }
}

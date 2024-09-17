<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FieldsForOpendatabotTransportTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_powers', function (Blueprint $table) {
            $table->integer('capacity')->nullable();
        });

        Schema::table('transport_categories', function (Blueprint $table) {
            $table->string('kind')->nullable();
        });

        \Illuminate\Support\Facades\DB::update("UPDATE transport_categories SET kind = 'Легковий, Легковой' WHERE alias = 'car'");
        \Illuminate\Support\Facades\DB::update("UPDATE transport_categories SET kind = 'Електромобіль' WHERE alias = 'ecar'");
        \Illuminate\Support\Facades\DB::update("UPDATE transport_categories SET kind = 'Мотоцикл, моторолер, мотороллер, мото' WHERE alias = 'moto'");
        \Illuminate\Support\Facades\DB::update("UPDATE transport_categories SET kind = 'автобус' WHERE alias = 'bus'");
        \Illuminate\Support\Facades\DB::update("UPDATE transport_categories SET kind = 'Вантажний, Грузовой' WHERE alias = 'truck'");
        \Illuminate\Support\Facades\DB::update("UPDATE transport_categories SET kind = 'Причіп, трейлер, Прицеп' WHERE alias = 'trailer'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transport_powers', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });

        Schema::table('transport_categories', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
}

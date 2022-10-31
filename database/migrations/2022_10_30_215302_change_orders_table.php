<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('territory')->nullable();
            $table->string('sport')->nullable();
            $table->string('target')->nullable();
            $table->boolean('with_covid')->nullable();
            $table->boolean('with_greencard')->nullable();
            $table->boolean('epolis')->nullable();
            $table->boolean('multiple_trip')->nullable();

            $table->foreignIdFor(\App\Models\VzrRangeDay::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('territory');
            $table->dropColumn('sport');
            $table->dropColumn('target');
            $table->dropColumn('with_covid');
            $table->dropColumn('with_greencard');
            $table->dropColumn('epolis');
            $table->dropColumn('multiple_trip');

            $table->dropForeign('orders_vzr_range_day_id_foreign');
            $table->dropColumn('vzr_range_day_id');
        });
    }
}

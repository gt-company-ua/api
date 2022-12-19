<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PromocodeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Promocode::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->decimal('full_price')
                ->after('price')
                ->nullable()
                ->unsigned();
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
            $table->dropForeign('orders_promocode_id_foreign');
            $table->dropColumn('promocode_id');
            $table->dropColumn('full_price');
        });
    }
}

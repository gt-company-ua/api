<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDraftMarkOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('draft')->default(false);
            $table->boolean('draft_sent')->default(false);
        });

        Schema::table('order_tourists', function (Blueprint $table) {
            $table->string('full_name')->nullable()->change();
            $table->string('doc_number')->nullable()->change();
            $table->date('birth')->nullable()->change();
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
            $table->dropColumn('draft');
        });
    }
}

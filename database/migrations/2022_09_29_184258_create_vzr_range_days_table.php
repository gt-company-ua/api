<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVzrRangeDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vzr_range_days', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\VzrRange::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->smallInteger('days')->unsigned();
            $table->decimal('sum', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vzr_range_days');
    }
}

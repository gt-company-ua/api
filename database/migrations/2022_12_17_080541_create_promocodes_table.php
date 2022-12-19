<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promocodes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->decimal('discount');
            $table->enum('type', ['amount', 'percent'])->default('amount');
            $table->timestamp('expired_at')->nullable();
            $table->integer('max_uses')->unsigned()->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('greencard')->default(false);
            $table->boolean('osago')->default(false);
            $table->boolean('kasko')->default(false);
            $table->boolean('vzr')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promocodes');
    }
}

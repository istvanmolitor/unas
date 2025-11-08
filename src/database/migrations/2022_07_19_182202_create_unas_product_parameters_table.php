<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnasProductParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unas_product_parameters', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('unas_shop_id');
            $table->foreign('unas_shop_id')->references('id')->on('unas_shops');

            $table->string('name');

            $table->string('type');

            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages');

            $table->unsignedInteger('order')->default(0);

            $table->unsignedBigInteger('remote_id')->nullable();
            $table->boolean('changed')->default(false);
            $table->softDeletes();

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
        Schema::dropIfExists('unas_product_parameters');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnasProductImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unas_product_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('unas_product_id');
            $table->foreign('unas_product_id')->references('id')->on('unas_products');

            $table->string('image_url')->nullable();
            $table->boolean('is_main')->default(false);
            $table->integer('sort')->default(0);

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
        Schema::dropIfExists('unas_product_images');
    }
}

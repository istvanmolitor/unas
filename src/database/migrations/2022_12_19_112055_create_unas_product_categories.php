<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnasProductCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unas_product_categories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('unas_shop_id');
            $table->foreign('unas_shop_id')->references('id')->on('unas_shops');

            $table->unsignedBigInteger('parent_id');

            $table->string('name');
            $table->string('title')->nullable();
            $table->string('keywords')->nullable();
            $table->text('description')->nullable();

            $table->boolean('display_page')->default(true);
            $table->boolean('display_menu')->default(true);

            $table->string('image_url')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();

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
        Schema::dropIfExists('unas_product_categories');
    }
}

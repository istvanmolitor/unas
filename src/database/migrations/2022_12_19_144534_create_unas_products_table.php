<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnasProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unas_products', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('unas_shop_id');
            $table->foreign('unas_shop_id')->references('id')->on('unas_shops');

            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('sku');

            $table->unsignedBigInteger('product_unit_id');
            $table->foreign('product_unit_id')->references('id')->on('product_units');

            $table->decimal('price', 11)->nullable();

            $table->bigInteger('stock')->nullable();

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
        Schema::dropIfExists('unas_products');
    }
}

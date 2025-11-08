<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnasProductCategoryProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unas_product_category_products', function (Blueprint $table) {
            $table->unsignedBigInteger('unas_product_id');
            $table->foreign('unas_product_id')->references('id')->on('unas_products');

            $table->unsignedBigInteger('unas_product_category_id');
            $table->foreign('unas_product_category_id', 'spc')->references('id')->on('unas_product_categories');

            $table->primary(['unas_product_id', 'unas_product_category_id'], 'unas_product_pcp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unas_product_category_products');
    }
}

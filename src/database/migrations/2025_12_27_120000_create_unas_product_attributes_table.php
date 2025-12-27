<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnasProductAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unas_product_attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('unas_product_id');
            $table->foreign('unas_product_id')->references('id')->on('unas_products');

            $table->unsignedBigInteger('product_field_option_id');
            $table->foreign('product_field_option_id')->references('id')->on('product_field_options');

            $table->integer('sort');

            $table->primary(['unas_product_id', 'product_field_option_id'], 'unas_product_attribute_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unas_product_attributes');
    }
}


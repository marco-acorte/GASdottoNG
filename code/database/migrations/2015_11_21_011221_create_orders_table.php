<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->string('supplier_id');
            $table->integer('aggregate_id')->unsigned();
            $table->string('comment')->nullable();
            $table->date('start')->useCurrent();
            $table->date('end')->useCurrent();
            $table->date('shipping')->nullable();
            $table->enum('status', ['suspended', 'open', 'closed', 'shipped', 'archived']);
            $table->string('discount')->nullable();
            $table->string('transport')->nullable();
            $table->integer('payment_id')->nullable();
            $table->date('last_notify')->nullable();
        });

        Schema::create('order_product', function (Blueprint $table) {
            $table->string('order_id');
            $table->string('product_id');
            $table->boolean('discount_enabled')->default(true);

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->primary(['order_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::drop('order_product');
        Schema::drop('orders');
    }
}

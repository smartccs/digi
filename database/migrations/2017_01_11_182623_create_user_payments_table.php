<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_id');
            $table->string('payment_id');
            $table->string('payment_mode');
            $table->integer('total_time');
            $table->float('base_price');
            $table->float('time_price');
            $table->float('tax_price');
            $table->float('commission_price');
            $table->float('total');
            $table->integer('status');
            $table->integer('promo_code_id');
            $table->float('trip_fare');
            $table->string('promo_code')->nullable();
            $table->float('offer_amount');
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
        Schema::dropIfExists('user_payments');
    }
}

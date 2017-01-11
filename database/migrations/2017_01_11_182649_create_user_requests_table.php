<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id');
            $table->integer('user_id');
            $table->integer('current_provider');
            $table->integer('confirmed_provider');
            $table->dateTime('request_start_time');
            $table->integer('later');
            $table->integer('later_status');
            $table->integer('user_later_status');
            $table->dateTime('requested_time');
            $table->integer('request_type');
            $table->integer('request_meta_id');
            $table->integer('provider_status');
            $table->string('after_image');
            $table->string('before_image');
            $table->double('s_latitude',15,8);
            $table->double('s_longitude',15,8);
            $table->double('d_latitude',15,8);
            $table->double('d_longitude',15,8);
            $table->tinyInteger('is_paid');
            $table->string('s_address');
            $table->string('d_address');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('amount');
            $table->integer('status');
            $table->float('wallet_amount')->default(0);
            $table->integer('is_promo_code');
            $table->integer('promo_code_id');
            $table->string('promo_code');
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
        Schema::dropIfExists('user_requests');
    }
}

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
            $table->integer('provider_id')->default(0);
            $table->integer('user_id');
            $table->integer('current_provider')->default(0);
            $table->integer('confirmed_provider')->default(0);
            $table->dateTime('request_start_time');
            $table->integer('later')->default(0);
            $table->integer('later_status')->default(0);
            $table->integer('user_later_status')->default(0);
            $table->dateTime('requested_time')->nullable();
            $table->integer('request_type');
            $table->integer('request_meta_id')->default(0);
            $table->integer('provider_status')->default(0);
            $table->string('after_image')->nullable();
            $table->string('before_image')->nullable();
            $table->double('s_latitude',15,8);
            $table->double('s_longitude',15,8);
            $table->double('d_latitude',15,8)->nullable();
            $table->double('d_longitude',15,8)->nullable();
            $table->tinyInteger('is_paid')->default(0);
            $table->string('s_address')->nullable();
            $table->string('d_address')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('amount')->default(0);
            $table->integer('status')->default(0);
            $table->float('wallet_amount')->default(0);
            $table->integer('is_promo_code')->default(0);
            $table->integer('promo_code_id')->default(0);
            $table->string('promo_code')->nullable();
            $table->float('offer_amount')->nullable();
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

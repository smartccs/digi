<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('payment_mode');
            $table->integer('default_card');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('picture');
            $table->string('device_token');
            $table->enum('device_type',array('android','ios'));
            $table->enum('login_by',array('manual','facebook','google'));
            $table->string('social_unique_id');
            $table->enum('gender',array('male','female','others'));
            $table->string('mobile');
            $table->double('latitude', 15, 8);
            $table->double('longitude',15,8);
            $table->string('paypal_email');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->string('country');
            $table->float('wallet_balance')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}

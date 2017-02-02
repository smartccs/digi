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
            $table->integer('user_id');
            $table->integer('provider_id')->default(0);
            $table->integer('current_provider_id');
            $table->integer('service_type_id');
            
            $table->enum('status', [
                    'CREATED',
                    'SEARCHING',
                    'CANCELLED',
                    'ASSIGNED', 
                    'STARTED',
                    'ARRIVED',
                    'PICKEDUP',
                    'DROPPED',
                    'PAID',
                    'COMPLETED',
                ]);

            $table->enum('cancelled_by', ['USER', 'PROVIDER']);

            $table->double('distance', 15, 8);
            
            $table->string('s_address')->nullable();
            $table->double('s_latitude', 15, 8);
            $table->double('s_longitude', 15, 8);
            
            $table->string('d_address')->nullable();
            $table->double('d_latitude', 15, 8);
            $table->double('d_longitude', 15, 8);
            
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('schedule_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id');
            $table->string('document_id');
            $table->string('url');
            $table->string('unique_id');
            $table->enum('status', ['MISSING', 'ASSESSING', 'ACTIVE']);
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
        Schema::dropIfExists('provider_documents');
    }
}

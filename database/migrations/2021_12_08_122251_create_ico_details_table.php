<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcoDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ico_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('crypto_id',250);
            // $table->foreign('common_id')->references('unique_id')->on('ico');
            // $table->string('extra_data',500);
           $table->string('name',250);
            $table->string('symbol',250);
            $table->string('slug',250);
            $table->string('status',250);
            $table->json('extra_data');
            $table->unique('crypto_id');
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
        Schema::dropIfExists('ico_details');
    }
}

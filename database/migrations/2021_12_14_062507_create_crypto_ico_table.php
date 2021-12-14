<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoIcoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_ico', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id',250);
            $table->string('crypto_id',250);
            $table->decimal('icoPriceUsd',15,6);
            $table->string('currentStage',200);
            $table->string('exchangeName',250);
            $table->string('launchpadUrl',250);
            $table->string('contracts_name',250);
            $table->string('goal',250);
            $table->dateTimeTz('startdate', $precision = 0);
            $table->dateTimeTz('enddate', $precision = 0);
            // $table->string('startdate',250);
            // $table->string('enddate',250);
            $table->string('name',250);
            $table->string('symbol',100);
            $table->string('slug',200);
            $table->string('logo',250);
            $table->string('cmc_logo',250);
            $table->string('status',250);
          //  $table->json('extra_data');
            $table->unique('unique_id');
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
        Schema::dropIfExists('crypto_ico');
    }
}

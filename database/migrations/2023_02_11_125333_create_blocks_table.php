<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->increments("id")->from(1000000);
            $table->unsignedInteger("parkingLotId");
            $table-> string("nameBlock",50);
            $table->enum('carType',['4slot','5-7slot','16slot','29-30slot','35-47slot']);
            $table->string("blockCode")->unique();
            $table->string("desc");
            $table->double("price");
            $table->foreign('parkingLotId')->references('id')->on('parking_lots')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blocks');
    }
};

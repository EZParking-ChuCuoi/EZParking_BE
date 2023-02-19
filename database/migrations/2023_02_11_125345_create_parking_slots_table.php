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
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->increments("id")->from(100000000);
            $table->unsignedInteger("blockId");
            $table->string("slotCode");
            $table->boolean("status");
            $table->enum('carType',['4-7SLOT','16-29SLOT','30-47SLOT']);
            $table->string("desc");
            $table->double("price");
            $table->foreign('blockId')->references('id')->on('blocks')->onDelete('cascade');
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
        Schema::dropIfExists('parking_slots');
    }
};

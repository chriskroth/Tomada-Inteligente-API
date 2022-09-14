<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("plug_id");
            $table->unsignedInteger("time")->comment("Tempo, em segundos, que ficará ligado");
            $table->boolean("emit_sound")->default(false);
            $table->dateTime("start_date")->nullable(true);
            $table->dateTime("end_date")->nullable(true);
            $table->float("consumption")->nullable(true)->comment("Consumo de energia");
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('plug_id')->references('id')->on('plugs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};

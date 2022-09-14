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
        Schema::create('users_has_plugs', function (Blueprint $table) {
//            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("plug_id");
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['user_id', 'plug_id']);
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
        Schema::dropIfExists('users_has_plugs');
    }
};

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
        if (!Schema::hasTable('user_event'))
            Schema::create('user_event', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('event_id');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
                $table->foreign('event_id')->references('id')->on('event')->cascadeOnDelete();
                $table->unique(['user_id', 'event_id']);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_event');
    }
};

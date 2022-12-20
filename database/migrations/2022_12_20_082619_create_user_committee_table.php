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
        if (!Schema::hasTable('user_committee'))
            Schema::create('user_committee', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('committee_id');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('user')->cascadeOnDelete();
                $table->foreign('committee_id')->references('id')->on('committee')->cascadeOnDelete();
                $table->unique(['user_id', 'committee_id']);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_committee');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('permission');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('permission'))
            Schema::create('permission', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->string('controller');
                $table->string('action');
                $table->timestamps();

                $table->unique(['role_id', 'controller', 'action']);
                $table->foreign('role_id')->references('id')->on('role')->cascadeOnDelete();
            });
    }
};

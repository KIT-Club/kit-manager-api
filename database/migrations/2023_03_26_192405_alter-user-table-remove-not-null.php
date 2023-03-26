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
        Schema::table('user', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('class')->nullable()->change();
            $table->string('major')->nullable()->change();
            $table->string('birthday')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('class')->nullable(false)->change();
            $table->string('major')->nullable(false)->change();
            $table->string('birthday')->nullable(false)->change();
        });
    }
};

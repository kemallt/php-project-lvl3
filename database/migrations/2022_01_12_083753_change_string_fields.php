<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStringFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('url_checks', function (Blueprint $table) {
            $table->string('h1', 500)->nullable()->change();
            $table->string('title', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('url_checks', function (Blueprint $table) {
            $table->string('h1')->nullable()->change();
            $table->string('title')->nullable()->change();
        });
    }
}

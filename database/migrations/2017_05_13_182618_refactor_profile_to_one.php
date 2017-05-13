<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorProfileToOne extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['us_profile', 'eu_profile', 'kr_profile']);
            $table->string('avatar_url');
            $table->integer('rank');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn(['avatar_url', 'rank']);
            $table->string('us_profile')->nullable();
            $table->string('eu_profile')->nullable();
            $table->string('kr_profile')->nullable();
        });
    }
}

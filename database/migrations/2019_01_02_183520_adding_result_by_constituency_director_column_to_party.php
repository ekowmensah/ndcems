<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingResultByConstituencyDirectorColumnToParty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('party_election_result', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();
            $table->string("result_by_constituency")->nullable();

            //
        });
        Schema::table('election_result', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('party_election_result', function (Blueprint $table) {
            $table->dropColumn("result_by_constituency");
        });
    }
}

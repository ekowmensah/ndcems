<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatingElectionResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('election_result', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('polling_station_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('user_type_id')->unsigned();
            $table->integer('country_id')->unsigned();
            $table->integer('region_id')->unsigned();

            $table->integer('constituency_id')->unsigned();
            $table->integer('electoral_area_id')->unsigned();
            $table->integer('election_type_id')->unsigned();
            //$table->integer('party_id')->unsigned();

            $table->integer('election_start_up_id')->unsigned();

            $table->string("obtained_votes");
            $table->string("total_ballot");
            $table->string("total_rejected_ballot")->nullable();

        });
        /* Schema::table('election_result', function (Blueprint $table) {
            $table->foreign('polling_station_id')->references('id')->on('PollingStation');
            $table->foreign('user_id')->references('id')->on('users')->comment("Polling Agent");
            $table->foreign('user_type_id')->references('id')->on('user_type');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('region_id')->references('id')->on('region');
            $table->foreign('constituency_id')->references('id')->on('constituency');
            $table->foreign('electoral_area_id')->references('id')->on('ElectoralArea');
            $table->foreign('election_type_id')->references('id')->on('election_type');
            $table->foreign('party_id')->references('id')->on('political_party');
            $table->foreign('candidate_id')->references('id')->on('candidates');
        }); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('election_result');

    }
}

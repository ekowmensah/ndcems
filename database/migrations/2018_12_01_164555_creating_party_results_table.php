<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatingPartyResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('party_election_result', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('election_result_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('polling_station_id')->unsigned();
            $table->integer('party_id')->unsigned();
            $table->integer('candidate_id')->unsigned();

            $table->string("obtained_vote");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('party_election_result');

    }
}

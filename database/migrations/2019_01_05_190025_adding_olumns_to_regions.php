<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingOlumnsToRegions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('party_election_result', function (Blueprint $table) {

            $table->integer('country_id')->unsigned()->nullable();
            $table->integer('region_id')->unsigned()->nullable();
            $table->integer('constituency_id')->unsigned()->nullable();
            $table->integer('electoral_area_id')->unsigned()->nullable();
            //
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

            $table->dropColumn('country_id');
            $table->dropColumn('region_id');
            $table->dropColumn('constituency_id');
            $table->dropColumn('electoral_area_id');
        });
    }
}

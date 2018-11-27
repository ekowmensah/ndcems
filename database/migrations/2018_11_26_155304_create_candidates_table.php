<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("photo")->nullable();
            $table->string("dob")->nullable()->commend("Date Of birth");
            $table->string("election_id")->nullable();
            $table->string("personal")->nullable();

            $table->string("party_id")->nullable();
            $table->string("region_id")->nullable();
            $table->string("constituency_id")->nullable();
            $table->string("polling_station_id")->nullable();
            $table->string("is_disabled")->default(0);
            $table->string("id_no")->nullable();
            $table->string("phone")->nullable();
            $table->string("electoral_area_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidates');
    }
}

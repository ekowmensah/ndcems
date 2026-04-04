<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingElectionStartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('election_startup_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string("election_name")->nullable();
            $table->string("start")->nullable();
            $table->string("end")->nullable();
            $table->string("total_constituency")->nullable();
            $table->string("total_electral")->nullable();
            $table->string("total_polling")->nullable();
            $table->string("total_voters")->nullable();
            $table->boolean("status")->nullable()->comment("if 0 then stoped if 1 then started");
            $table->string("election_type_id")->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('election_startup_detail');
    }
}

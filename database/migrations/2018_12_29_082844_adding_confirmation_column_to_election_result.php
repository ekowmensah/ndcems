<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingConfirmationColumnToElectionResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('election_result', function (Blueprint $table) {
            $table->boolean("verify_by_constituency")->default(0);
            $table->boolean("verify_by_regional")->default(0);
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
        Schema::table('election_result', function (Blueprint $table) {
            $table->dropColumn("verify_by_constituency");
            $table->dropColumn("verify_by_constituency");

        });
    }
}

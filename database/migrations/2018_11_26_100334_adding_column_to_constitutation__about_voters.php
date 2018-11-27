<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingColumnToConstitutationAboutVoters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('constituency', function (Blueprint $table) {
            //$table->string('total_voters')->nullable();
            $table->string('total_candidates')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('constituency', function (Blueprint $table) {
            //$table->dropColumn('total_voters');
            $table->dropColumn('total_candidates');
        });
    }
}

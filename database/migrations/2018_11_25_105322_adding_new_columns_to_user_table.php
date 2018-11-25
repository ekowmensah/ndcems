<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingNewColumnsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country_id')->nullable();
            $table->string('region_id')->nullable();
            $table->string('constituency_id')->nullable();
            $table->string('polling_station_id')->nullable();
            $table->string('electoralarea_id')->nullable();
            $table->string('added_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country_id');
            $table->dropColumn('region_id');
            $table->dropColumn('constituency_id');
            $table->dropColumn('polling_station_id');
            $table->dropColumn('electoralarea_id');
            $table->dropColumn('added_by');
        });
    }
}

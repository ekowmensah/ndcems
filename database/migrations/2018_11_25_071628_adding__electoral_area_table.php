<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingElectoralAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ElectoralArea', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('country_id')->nullable();
            $table->string('region_id')->nullable();
            $table->string('constituency_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ElectoralArea');
    }
}

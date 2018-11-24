<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingPhoneNoColumnToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
           // $table->string('username')->nullable()->change();
           $table->dropColumn('username');
            $table->string('phoneno')->nullable();
            $table->string('constituency')->nullable();
            $table->string('gender')->nullable();
           // $table->string('username')->nullable();
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
            //$table->string('username')->unique()->change();
            $table->dropColumn('phoneno');
            $table->dropColumn('constituency');
            $table->dropColumn('gender');
            //$table->dropColumn('username');
        });
    }
}

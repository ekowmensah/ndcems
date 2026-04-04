<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Sms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_process', function (Blueprint $table) {
            $table->increments('id');
			$table->string("phone");
			$table->integer("type");
			$table->string("stage");
			$table->string("user_id")->nullable();
			$table->string("data")->nullable();
			$table->text("obj");
            $table->timestamps();
			$table->index("phone");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('message_process');
    }
}

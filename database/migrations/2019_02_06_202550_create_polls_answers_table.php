<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollsAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('polls_answers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->increments('id');
            $table->unsignedInteger("user_id");
            $table->unsignedInteger("poll_id");
            $table->unsignedInteger("choice_id");
            $table->string("ip_address", 100);
            $table->timestamp("timestamp")->useCurrent();
        });

        Schema::table('polls_answers', function (Blueprint $table) {
            $table->foreign("user_id", "answer_user_id")->references("id")->on("users");
            $table->foreign("poll_id", "answer_poll_id")->references("id")->on("polls");
            $table->foreign("choice_id", "answer_choice_id")->references("id")->on("polls_choices");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('polls_answers');
    }
}

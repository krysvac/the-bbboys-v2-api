<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeebAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weeb_answers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->increments('id');
            $table->unsignedInteger("user_id");
            $table->unsignedInteger("choice_id");
            $table->string("ip_address", 100);
            $table->dateTime("timestamp")->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::table('weeb_answers', function (Blueprint $table) {
            $table->foreign("user_id", "weeb_answer_user_id")->references("id")->on("users");
            $table->foreign("choice_id", "weeb_answer_choice_id")->references("id")->on("weeb_choices");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weeb_answers');
    }
}

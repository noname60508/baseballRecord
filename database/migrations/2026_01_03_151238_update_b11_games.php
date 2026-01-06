<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 資料表欄位增加
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:
     */
    public function up()
    {
        Schema::table('B11_games', function (Blueprint $table) {
            $table->integer('homeAway')->nullable()->comment('先攻後攻(1:先攻 2:後攻)')->after('endTime');
            $table->integer('score')->nullable()->comment('我方得分')->after('homeAway');
            $table->integer('enemyScore')->nullable()->comment('對方得分')->after('score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('B11_games', function (Blueprint $table) {
            $table->dropColumn('homeAway');
            $table->dropColumn('score');
            $table->dropColumn('enemyScore');
        });
    }
};

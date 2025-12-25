<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:比賽資料表
     */
    public function up()
    {
        Schema::create('B11_games', function (Blueprint $table) {
            $table->id()->comment('流水號');
            $table->integer('user_id')->nullable()->comment('使用者ID');
            $table->integer('Z00_season_id')->nullable()->comment('賽季ID');
            $table->integer('Z00_team_id')->nullable()->comment('我方球隊ID');
            $table->integer('Z00_team_id_enemy')->nullable()->comment('對方球隊ID');
            $table->integer('Z00_field_id')->nullable()->comment('場地ID');
            $table->date('gameDate')->nullable()->comment('比賽日期');
            $table->time('startTime')->nullable()->comment('比賽開始時間');
            $table->time('endTime')->nullable()->comment('比賽結束時間');
            $table->integer('result')->nullable()->comment('比賽結果(1:勝,2:敗,3:和)');
            $table->string('memo')->nullable()->comment('備註');

            commonMigration::basicTimestamp($table);
            /** ***增加欄位***
             */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('B11_games');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:打擊成績
     */
    public function up()
    {
        Schema::create('B21_batterResult', function (Blueprint $table) {
            $table->id()->comment('流水號');
            $table->integer('game_id')->nullable()->comment('比賽id');
            $table->integer('user_id')->nullable()->comment('使用者id');
            $table->string('pitcher')->nullable()->comment('投手名稱');
            $table->integer('Z00_matchupResultList_id')->nullable()->comment('打擊結果id');
            $table->integer('Z00_location_id')->nullable()->comment('擊球落點id');
            $table->integer('Z00_BallInPlayType_id')->nullable()->comment('擊球型態id');
            $table->integer('RBI')->nullable()->comment('打點');
            $table->integer('RISP')->nullable()->comment('得點圈打擊0:否 1:是');

            commonMigration::basicTimestamp($table);
            /** ***增加欄位***
             * $table->integer('orderNo')->nullable()->comment('打席順序');
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
        Schema::dropIfExists('B21_batterResult');
    }
};

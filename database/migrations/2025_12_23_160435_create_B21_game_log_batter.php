<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:選手逐場打擊成績
     */
    public function up()
    {
        Schema::create('B21_gameLogBatter', function (Blueprint $table) {
            $table->id()->comment('流水號');
            $table->integer('game_id')->nullable()->comment('比賽ID');
            $table->integer('user_id')->nullable()->comment('使用者ID');
            $table->integer('PA')->default(0)->comment('打席數');
            $table->integer('AB')->default(0)->comment('打數');
            $table->integer('RBI')->default(0)->comment('打點');
            $table->integer('R')->default(0)->comment('得分');
            $table->integer('single')->default(0)->comment('一壘安打數');
            $table->integer('double')->default(0)->comment('二壘安打數');
            $table->integer('triple')->default(0)->comment('三壘安打數');
            $table->integer('HR')->default(0)->comment('全壘打數');
            $table->integer('BB')->default(0)->comment('四壞球數');
            $table->integer('IBB')->default(0)->comment('故意四壞球數');
            $table->integer('HBP')->default(0)->comment('觸身球數');
            $table->integer('SO')->default(0)->comment('三振數');
            $table->integer('SH')->default(0)->comment('犧牲觸擊數');
            $table->integer('SF')->default(0)->comment('高飛犧牲打數');
            $table->integer('SB')->default(0)->comment('盜壘成功數');
            $table->integer('CS')->default(0)->comment('盜壘失敗數');

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
        Schema::dropIfExists('B21_gameLogBatter');
    }
};

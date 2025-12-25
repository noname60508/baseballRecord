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
        Schema::create('C11_gameLogBatter', function (Blueprint $table) {
            $table->id()->comment('流水號');
            $table->integer('game_id')->nullable()->comment('比賽ID');
            $table->integer('user_id')->nullable()->comment('使用者ID');
            $table->integer('PA')->nullable()->comment('打席數');
            $table->integer('AB')->nullable()->comment('打數');
            $table->integer('RBI')->nullable()->comment('打點');
            $table->integer('R')->nullable()->comment('得分');
            $table->integer('1B')->nullable()->comment('一壘安打數');
            $table->integer('2B')->nullable()->comment('二壘安打數');
            $table->integer('3B')->nullable()->comment('三壘安打數');
            $table->integer('HR')->nullable()->comment('全壘打數');
            $table->integer('BB')->nullable()->comment('四壞球數');
            $table->integer('IBB')->nullable()->comment('故意四壞球數');
            $table->integer('HBP')->nullable()->comment('觸身球數');
            $table->integer('SO')->nullable()->comment('三振數');
            $table->integer('SH')->nullable()->comment('犧牲觸擊數');
            $table->integer('SF')->nullable()->comment('高飛犧牲打數');
            $table->integer('SB')->nullable()->comment('盜壘成功數');
            $table->integer('CS')->nullable()->comment('盜壘失敗數');

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
        Schema::dropIfExists('C11_gameLogBatter');
    }
};

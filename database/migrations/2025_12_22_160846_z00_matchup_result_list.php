<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:對決結果列表資料表
     */
    public function up()
    {
        Schema::create('Z00_matchupResultList', function (Blueprint $table) {
            $table->bigInteger('id')->primary()->unsigned()->comment('流水號');
            $table->string('code')->nullable()->comment('縮寫');
            $table->string('name')->nullable()->comment('結果名稱');
            $table->integer('isAtBat')->nullable()->comment('是否列入打席');
            $table->integer('isHit')->nullable()->comment('是否計算打擊率');
            $table->integer('isOnBase')->nullable()->comment('是否計算上壘率');
            $table->integer('totalBases')->nullable()->comment('安打壘包數');
            $table->integer('orderNo')->nullable()->comment('排序');

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
        Schema::dropIfExists('Z00_matchupResultList');
    }
};

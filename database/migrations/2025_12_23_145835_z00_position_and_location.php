<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:擊球落點與守備位置資料表
     */
    public function up()
    {
        Schema::create('Z00_positionAndLocation', function (Blueprint $table) {
            $table->bigInteger('id')->primary()->unsigned()->comment('流水號');
            $table->string('code')->nullable()->comment('位置或守備位置縮寫');
            $table->string('name')->nullable()->comment('位置或守備位置名稱');
            $table->integer('isPosition')->nullable()->comment('是否為守備位置');
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
        Schema::dropIfExists('Z00_positionAndLocation');
    }
};

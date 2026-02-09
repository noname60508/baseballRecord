<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:擊球型態資料表
     */
    public function up()
    {
        Schema::create('Z00_ballInPlayType', function (Blueprint $table) {
            $table->bigInteger('id')->primary()->unsigned()->comment('流水號');
            $table->string('code')->nullable()->comment('縮寫');
            $table->string('name')->nullable()->comment('名稱');
            $table->string('jaName')->nullable()->comment('日文名稱');
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
        Schema::dropIfExists('Z00_ballInPlayType');
    }
};

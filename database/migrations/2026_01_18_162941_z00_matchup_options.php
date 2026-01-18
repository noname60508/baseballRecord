<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:對決結果對應選項資料表
     */
    public function up()
    {
        Schema::create('Z00_matchupOptions', function (Blueprint $table) {
            $table->id()->comment('流水號');
            $table->integer('Z00_matchupResultList_id')->nullable()->comment('對決結果列表資料表id');
            $table->string('ballTypeOptions')->nullable()->comment('擊球型態id選項');
            $table->string('locationOptions')->nullable()->comment('位置id選項');

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
        Schema::dropIfExists('Z00_matchupOptions');
    }
};

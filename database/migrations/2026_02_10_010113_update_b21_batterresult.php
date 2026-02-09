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
        Schema::table('B21_batterResult', function (Blueprint $table) {
            $table->string('jaDisplayName', 255)->nullable()->comment('顯示名稱(日文)')->after('displayName');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('B21_batterResult', function (Blueprint $table) {
            $table->dropColumn('jaDisplayName');
        });
    }
};

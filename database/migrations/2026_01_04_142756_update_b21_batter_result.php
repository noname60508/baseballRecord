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
            $table->integer('orderNo')->nullable()->comment('打席順序')->after('user_id');
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
            $table->dropColumn('orderNo');
        });
    }
};

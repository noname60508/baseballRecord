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
        Schema::table('users', function (Blueprint $table) {
            $table->string('account', 50)->comment('帳號')->after('id');
            $table->integer('failCount')->default(0)->comment('登入失敗次數')->after('remember_token');
            $table->datetime('failCooldown')->nullable()->comment('登入失敗冷卻時間')->after('failCount');
            $table->integer('isBan')->default(0)->comment('是否封鎖')->after('failCooldown');
            $table->timestamp('lastLoginAt')->nullable()->comment('最後登入時間')->after('isBan');
            $table->string('lastLoginIp', 45)->nullable()->comment('最後登入IP')->after('lastLoginAt');

            $table->softDeletes()->after('updated_at');
            $table->unsignedBigInteger('created_by')->nullable()->comment('資料建立人員');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('最後編輯人員');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('最後刪除人員');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account');
            $table->dropColumn('failCount');
            $table->dropColumn('failCooldown');
            $table->dropColumn('isBan');
            $table->dropColumn('lastLoginAt');
            $table->dropColumn('lastLoginIp');
            $table->dropSoftDeletes();
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('deleted_by');
        });
    }
};

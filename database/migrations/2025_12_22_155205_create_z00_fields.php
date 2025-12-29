<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:場地資料表
     */
    public function up()
    {
        Schema::create('Z00_fields', function (Blueprint $table) {
            $table->id()->comment('流水號');
            $table->integer('user_id')->nullable()->comment('使用者ID');
            $table->string('name')->nullable()->comment('場地名稱');

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
        Schema::dropIfExists('Z00_fields');
    }
};

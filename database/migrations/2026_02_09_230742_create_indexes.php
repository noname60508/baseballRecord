<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Utils\commonMigration;

return new class extends Migration
{
    /**
     * 參考網址:https://laravel.net.cn/docs/12.x/packages#migrations
     * 表名:index調整
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['account', 'email']);
        });
        Schema::table('B11_games', function (Blueprint $table) {
            $table->index(['user_id', 'Z00_season_id', 'Z00_team_id', 'Z00_team_id_enemy', 'Z00_field_id', 'gameResult', 'gameDate'], 'idx_composite_search');
        });
        Schema::table('B21_batterResult', function (Blueprint $table) {
            $table->index(['user_id']);
            $table->index(['game_id', 'user_id']);
        });
        Schema::table('B21_gameLogBatter', function (Blueprint $table) {
            $table->index(['user_id']);
            $table->index(['game_id', 'user_id']);
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
            $table->dropIndex(['account', 'email']);
        });
        Schema::table('B11_games', function (Blueprint $table) {
            $table->dropIndex('idx_composite_search');
        });
        Schema::table('B21_batterResult', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['game_id', 'user_id']);
        });
        Schema::table('B21_gameLogBatter', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['game_id', 'user_id']);
        });
    }
};

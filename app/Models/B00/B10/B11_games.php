<?php

namespace App\Models\B00\B10;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mattiverse\Userstamps\Traits\Userstamps;
use App\Models\Z00\Z00_seasons;
use App\Models\Z00\Z00_teams;
use App\Models\Z00\Z00_fields;
use App\Models\B00\B20\B21_gameLogBatter;

class B11_games extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Userstamps;

    // 資料表名稱:比賽資料表

    // 指定模型的表名
    protected $table = 'B11_games';
    // 白名單與黑名單擇一使用，即可使用create方法
    // 白名單:可批量新增欄位
    // protected $fillable=[];
    // 黑名單:不可批量新增欄位
    protected $guarded = [];

    public function seasonName()
    {
        return $this->belongsTo(Z00_seasons::class, 'Z00_season_id', 'id');
    }
    public function teamName()
    {
        return $this->belongsTo(Z00_teams::class, 'Z00_team_id', 'id');
    }
    public function teamNameEnemy()
    {
        return $this->belongsTo(Z00_teams::class, 'Z00_team_id_enemy', 'id');
    }
    public function fieldName()
    {
        return $this->belongsTo(Z00_fields::class, 'Z00_field_id', 'id');
    }

    public function batterGameLog()
    {
        return $this->hasOne(B21_gameLogBatter::class, 'game_id', 'id')->select('id', 'game_id', 'user_id', 'PA', 'AB', 'RBI', 'R', 'single', 'double', 'triple', 'HR', 'BB', 'IBB', 'HBP', 'SO', 'SH', 'SF', 'SB', 'CS');
    }
}

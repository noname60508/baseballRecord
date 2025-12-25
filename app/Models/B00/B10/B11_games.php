<?php

namespace App\Models\B00\B10;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mattiverse\Userstamps\Traits\Userstamps;

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
}

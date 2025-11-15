<?php

namespace App\Http\Utils;

use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\Storage;

class tools
{
    // 使用者頭像路徑系統化
    public static function userIconPathSystematics($userId): string
    {
        $row = 2000;
        $path = (int)(($userId - 1) / $row);
        return $path * $row + 1 . '_' . ($path + 1) * $row;
    }
}

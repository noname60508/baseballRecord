<?php

namespace App\Http\Utils;

use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\Storage;

use App\Models\Z00\Z00_ballInPlayType;
use App\Models\Z00\Z00_matchupResultList;
use App\Models\Z00\Z00_positionAndLocation;
use App\Models\B00\B20\B21_batterResult;
use App\Models\B00\B20\B21_gameLogBatter;

class tools
{
    private static $Z00_ballInPlayType = [];
    private static $Z00_matchupResultList = [];
    private static $Z00_positionAndLocation = [];
    // 使用者頭像路徑系統化
    public static function userIconPathSystematics($userId): string
    {
        $row = 2000;
        $path = (int)(($userId - 1) / $row);
        return $path * $row + 1 . '_' . ($path + 1) * $row;
    }

    private static function getZ00Table()
    {
        if (count(self::$Z00_ballInPlayType) == 0) {
            self::$Z00_ballInPlayType = Z00_ballInPlayType::select('id', 'name')->get();
        }
        if (count(self::$Z00_matchupResultList) == 0) {
            self::$Z00_matchupResultList = Z00_matchupResultList::select('id', 'name', 'isAtBat')->get();
        }
        if (count(self::$Z00_positionAndLocation) == 0) {
            self::$Z00_positionAndLocation = Z00_positionAndLocation::select('id', 'name')->get();
        }
    }

    public static function getDisplayName(int $Z00_matchupResultList_id, int $Z00_location_id, int $Z00_BallInPlayType_id): string
    {
        self::getZ00Table();
        $Z00_matchupResultList = collect(self::$Z00_matchupResultList)->keyBy('id');
        $Z00_positionAndLocation = collect(self::$Z00_positionAndLocation)->keyBy('id');
        $Z00_ballInPlayType = collect(self::$Z00_ballInPlayType)->keyBy('id');

        // 如果沒紀錄就回傳空字串
        $Z00_positionAndLocation[0] = ['name' => ''];
        $Z00_ballInPlayType[0] = ['name' => ''];

        match ($Z00_matchupResultList_id) {
            // 右外野全壘打/左外野高飛犧牲打...
            7, 8, 9 => $displayName = $Z00_positionAndLocation[$Z00_location_id]['name'] . $Z00_matchupResultList[$Z00_matchupResultList_id]['name'],
            // 三振/保送...
            10, 11, 12, 13, 14, 15 => $displayName = $Z00_matchupResultList[$Z00_matchupResultList_id]['name'],
            // 游擊滾地球出局/中外野高飛球二壘安打...
            default => $displayName = $Z00_positionAndLocation[$Z00_location_id]['name'] . $Z00_ballInPlayType[$Z00_BallInPlayType_id]['name'] . $Z00_matchupResultList[$Z00_matchupResultList_id]['name'],
        };
        return $displayName;
    }

    public static function B21_gameLogBatterUpdate($game_id)
    {
        if (count(self::$Z00_matchupResultList) == 0) {
            self::$Z00_matchupResultList = Z00_matchupResultList::select('id', 'name', 'isAtBat')->get();
        }
        $Z00_matchupResultList = collect(self::$Z00_matchupResultList)->keyBy('id');
        $B21_batterResult = B21_batterResult::where('game_id', $game_id)->get();

        $B21_gameLogBatterUpdateArr = [
            'PA'     => 0,
            'AB'     => 0,
            'RBI'    => 0,
            'single' => 0,
            'double' => 0,
            'triple' => 0,
            'HR'     => 0,
            'BB'     => 0,
            'IBB'    => 0,
            'HBP'    => 0,
            'SO'     => 0,
            'SH'     => 0,
            'SF'     => 0,
        ];

        foreach ($B21_batterResult as $batterResult) {
            $matchupResult = $Z00_matchupResultList[$batterResult['Z00_matchupResultList_id']];
            $B21_gameLogBatterUpdateArr['RBI'] += $batterResult['RBI'];
            $B21_gameLogBatterUpdateArr['PA'] += 1;

            // 打數
            if (((int)$matchupResult['isAtBat']) === 1) {
                $B21_gameLogBatterUpdateArr['AB'] += 1;
            }

            match ($matchupResult['id']) {
                1, 2, 3, 15, 16, 17 => null, // 未列入統計欄位
                4 => $B21_gameLogBatterUpdateArr['single'] += 1, // 1B
                5 => $B21_gameLogBatterUpdateArr['double'] += 1, // 2B
                6 => $B21_gameLogBatterUpdateArr['triple'] += 1, // 3B
                7 => $B21_gameLogBatterUpdateArr['HR'] += 1, // HR
                8 => $B21_gameLogBatterUpdateArr['SF'] += 1, // 高飛犧牲打
                9 => $B21_gameLogBatterUpdateArr['SH'] += 1, // 犧牲觸擊
                10 => $B21_gameLogBatterUpdateArr['SO'] += 1, // 三振
                11 => $B21_gameLogBatterUpdateArr['SO'] += 1, // 不死三振
                12 => $B21_gameLogBatterUpdateArr['BB'] += 1, // 四壞球
                13 => $B21_gameLogBatterUpdateArr['IBB'] += 1, // 故意四壞球
                14 => $B21_gameLogBatterUpdateArr['HBP'] += 1, // 觸身球
            };
        }

        B21_gameLogBatter::where('game_id', $game_id)->update($B21_gameLogBatterUpdateArr);
        return $B21_gameLogBatterUpdateArr;
    }
}

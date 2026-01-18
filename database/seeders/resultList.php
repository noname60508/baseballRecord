<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Z00\Z00_matchupResultList;
use App\Models\Z00\Z00_ballInPlayType;
use App\Models\Z00\Z00_positionAndLocation;
use App\Models\Z00\Z00_matchupOptions;

class resultList extends Seeder
{
    /**
     * id不要換!!!id不要換!!!id不要換!!!
     * 可以改順序調整orderNo值但id不要換!!!
     */
    public function run(): void
    {
        /******************** Z00_matchupResultList(打擊結果) ********************/
        $Z00_matchupResultList = [
            [
                'id' => 1,
                'code' => 'O',
                'name' => '出局',
                'isAtBat' => 1,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
            ],
            [
                'id' => 2,
                'code' => 'ROE',
                'name' => '失誤上壘',
                'isAtBat' => 1,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            ],
            [
                'id' => 3,
                'code' => 'FC',
                'name' => '野手選擇上壘',
                'isAtBat' => 1,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            ],
            [
                'id' => 4,
                'code' => '1B',
                'name' => '一壘安打',
                'isAtBat' => 1,
                'isHit' => 1,
                'isOnBase' => 1,
                'totalBases' => 1,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            ],
            [
                'id' => 5,
                'code' => '2B',
                'name' => '二壘安打',
                'isAtBat' => 1,
                'isHit' => 1,
                'isOnBase' => 1,
                'totalBases' => 2,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            ],
            [
                'id' => 6,
                'code' => '3B',
                'name' => '三壘安打',
                'isAtBat' => 1,
                'isHit' => 1,
                'isOnBase' => 1,
                'totalBases' => 3,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            ],
            [
                'id' => 7,
                'code' => 'HR',
                'name' => '全壘打',
                'isAtBat' => 1,
                'isHit' => 1,
                'isOnBase' => 1,
                'totalBases' => 4,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            ],
            [
                'id' => 8,
                'code' => 'SF',
                'name' => '高飛犧牲打',
                'isAtBat' => 0,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 1,
                'ballTypeOptions' => [3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
            ],
            [
                'id' => 9,
                'code' => 'SH',
                'name' => '犧牲觸擊',
                'isAtBat' => 0,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 0, // 犧牲觸擊計算數據時不算打進場內
                'ballTypeOptions' => [1],
                'locationOptions' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'id' => 10,
                'code' => 'SO',
                'name' => '三振',
                'isAtBat' => 1,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 0,
                'ballTypeOptions' => [0],
                'locationOptions' => [0],
            ],
            [
                'id' => 11,
                'code' => 'SO/ROE',
                'name' => '不死三振',
                'isAtBat' => 1,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 0,
                'ballTypeOptions' => [0],
                'locationOptions' => [0],
            ],
            [
                'id' => 12,
                'code' => 'BB',
                'name' => '四壞球',
                'isAtBat' => 0,
                'isHit' => 0,
                'isOnBase' => 1,
                'totalBases' => 0,
                'ballInPlay' => 0,
                'ballTypeOptions' => [0],
                'locationOptions' => [0],
            ],
            [
                'id' => 13,
                'code' => 'IBB',
                'name' => '故意四壞球',
                'isAtBat' => 0,
                'isHit' => 0,
                'isOnBase' => 1,
                'totalBases' => 0,
                'ballInPlay' => 0,
                'ballTypeOptions' => [0],
                'locationOptions' => [0],
            ],
            [
                'id' => 14,
                'code' => 'HBP',
                'name' => '觸身球',
                'isAtBat' => 0,
                'isHit' => 0,
                'isOnBase' => 1,
                'totalBases' => 0,
                'ballInPlay' => 0,
                'ballTypeOptions' => [0],
                'locationOptions' => [0],
            ],
            [
                'id' => 15,
                'code' => 'CI',
                'name' => '妨礙打擊上壘',
                'isAtBat' => 0,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 0,
                'ballTypeOptions' => [0],
                'locationOptions' => [0],
            ],
            [
                'id' => 16,
                'code' => 'DP',
                'name' => '雙殺',
                'isAtBat' => 1,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
            ],
            [
                'id' => 17,
                'code' => 'TP',
                'name' => '三殺',
                'isAtBat' => 1,
                'isHit' => 0,
                'isOnBase' => 0,
                'totalBases' => 0,
                'ballInPlay' => 1,
                'ballTypeOptions' => [1, 2, 3, 4],
                'locationOptions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
            ],
        ];
        foreach ($Z00_matchupResultList as $key => $data) {
            $id = $data['id'];
            unset($data['id']);
            $ballTypeOptions = implode(',', $data['ballTypeOptions']);
            $locationOptions = implode(',', $data['locationOptions']);
            unset($data['ballTypeOptions']);
            unset($data['locationOptions']);

            $data['orderNo'] = $key + 1;
            Z00_matchupResultList::updateOrCreate(
                ['id' => $id],
                $data
            );

            Z00_matchupOptions::updateOrCreate(
                ['Z00_matchupResultList_id' => $id],
                [
                    'ballTypeOptions' => $ballTypeOptions,
                    'locationOptions' => $locationOptions,
                ]
            );
        }

        /******************** Z00_ballInPlayType(擊球型態) ********************/
        $Z00_ballInPlayType = [
            [
                'id' => 1,
                'code' => 'GB',
                'name' => '滾地球',
            ],
            [
                'id' => 2,
                'code' => 'LD',
                'name' => '平飛球',
            ],
            [
                'id' => 3,
                'code' => 'FB',
                'name' => '飛球',
            ],
            [
                'id' => 4,
                'code' => 'PU',
                'name' => '內野高飛球',
            ],
        ];
        foreach ($Z00_ballInPlayType as $key => $data) {
            $id = $data['id'];
            unset($data['id']);
            $data['orderNo'] = $key + 1;
            Z00_ballInPlayType::updateOrCreate(
                ['id' => $id],
                $data
            );
        }

        /******************** Z00_positionAndLocation(守備位置與落點) ********************/
        $Z00_positionAndLocation = [
            [
                'id' => 1,
                'code' => 'P',
                'name' => '投手',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 2,
                'code' => 'C',
                'name' => '捕手',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 3,
                'code' => '1B',
                'name' => '一壘',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 4,
                'code' => '2B',
                'name' => '二壘',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 5,
                'code' => '3B',
                'name' => '三壘',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 6,
                'code' => 'SS',
                'name' => '游擊',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 7,
                'code' => 'LF',
                'name' => '左外野',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 8,
                'code' => 'CF',
                'name' => '中外野',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 9,
                'code' => 'RF',
                'name' => '右外野',
                'isPosition' => 1,
                'isFairBall' => 1,
            ],
            [
                'id' => 10,
                'code' => 'LCF',
                'name' => '左中外野',
                'isPosition' => 0,
                'isFairBall' => 1,
            ],
            [
                'id' => 11,
                'code' => 'RCF',
                'name' => '中右外野',
                'isPosition' => 0,
                'isFairBall' => 1,
            ],
            [
                'id' => 12,
                'code' => '1BFoul',
                'name' => '一壘界外',
                'isPosition' => 0,
                'isFairBall' => 0,
            ],
            [
                'id' => 13,
                'code' => '3BFoul',
                'name' => '三壘界外',
                'isPosition' => 0,
                'isFairBall' => 0,
            ],
            [
                'id' => 14,
                'code' => 'LFFoul',
                'name' => '左外野界外',
                'isPosition' => 0,
                'isFairBall' => 0,
            ],
            [
                'id' => 15,
                'code' => 'RFFoul',
                'name' => '右外野界外',
                'isPosition' => 0,
                'isFairBall' => 0,
            ],
            [
                'id' => 16,
                'code' => 'CFoul',
                'name' => '捕手後方界外',
                'isPosition' => 0,
                'isFairBall' => 0,
            ],
        ];

        foreach ($Z00_positionAndLocation as $key => $data) {
            $id = $data['id'];
            unset($data['id']);
            $data['orderNo'] = $key + 1;
            Z00_positionAndLocation::updateOrCreate(
                ['id' => $id],
                $data
            );
        }
    }
}

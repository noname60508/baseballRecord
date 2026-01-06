<?php

namespace App\Http\Controllers\B00\B20;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\B00\B20\B21_batterResult;
use App\Models\B00\B20\B21_gameLogBatter;
use App\Models\Z00\Z00_matchupResultList;

use function PHPUnit\Framework\isInt;

class B21_battingResult extends Controller
{

    public $resultId;
    public function __construct()
    {
        $this->resultId = [
            4  => 'single', // 1B
            5  => 'double', // 2B
            6  => 'triple', // 3B
            7  => 'HR', // HR
            8  => 'SF', // 高飛犧牲打
            9  => 'SH', // 犧牲觸擊
            10 => 'SO', // 三振
            11 => 'SO', // 不死三振
            12 => 'BB', // 四壞球
            13 => 'IBB', // 故意四壞球
            14 => 'HBP', // 觸身球
        ];
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'game_id' => ['required', 'integer'],
            'result'  => ['required', 'array'],

            'result.*.orderNo'                  => ['required', 'integer'],
            'result.*.pitcher'                  => ['nullable', 'string'],
            'result.*.Z00_matchupResultList_id' => ['required', 'integer'],
            'result.*.Z00_location_id'          => ['nullable', 'integer'],
            'result.*.Z00_BallInPlayType_id'    => ['nullable', 'integer'],
            'result.*.RBI'                      => ['nullable', 'integer'],
            'result.*.RISP'                     => ['required', 'in:0,1'],
        ], [
            // 自訂回傳錯誤訊息
            'game_id' => '【game_id:比賽ID】必填且須為整數',
            'result'  => '【result:結果列表】必填且須為陣列',

            'result.*.orderNo'                  => '【result.orderNo:打席順序】必填且須為整數',
            'result.*.pitcher'                  => '【result.pitcher:投手名稱】須為字串',
            'result.*.Z00_matchupResultList_id' => '【result.Z00_matchupResultList_id:打擊結果id】必填且須為整數',
            'result.*.Z00_location_id'          => '【result.Z00_location_id:擊球落點id】須為整數',
            'result.*.Z00_BallInPlayType_id'    => '【result.Z00_BallInPlayType_id:擊球型態id】須為整數',
            'result.*.RBI'                      => '【result.RBI:打點】須為整數',
            'result.*.RISP'                     => '【result.RISP:得點圈打擊0:否 1:是】必填且須為整數',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            DB::beginTransaction();
            foreach ($request->input('result') as $key => $result) {
                $B21_batterResult[] = B21_batterResult::create([
                    'game_id'                     => $request->input('game_id'),
                    'user_id'                     => $request->user()->id,
                    'pitcher'                     => $result['pitcher'] ?? null,
                    'Z00_matchupResultList_id'    => $result['Z00_matchupResultList_id'],
                    'Z00_location_id'             => $result['Z00_location_id'] ?? 0,
                    'Z00_BallInPlayType_id'       => $result['Z00_BallInPlayType_id'] ?? 0,
                    'RBI'                         => $result['RBI'] ?? 0,
                    'RISP'                        => $result['RISP'],
                    'orderNo'                     => $result['orderNo'],
                ]);
            }

            $this->B21_gameLogBatterUpdate($request->input('game_id'));
            DB::commit();
            return response()->apiResponse();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->apiFail($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'game_id' => ['required', 'integer'],
            'result'  => ['required', 'array'],

            'result.*.id'                       => ['required', 'integer'],
            'result.*.orderNo'                  => ['nullable', 'integer'],
            'result.*.pitcher'                  => ['nullable', 'string'],
            'result.*.Z00_matchupResultList_id' => ['nullable', 'integer'],
            'result.*.Z00_location_id'          => ['nullable', 'integer'],
            'result.*.Z00_BallInPlayType_id'    => ['nullable', 'integer'],
            'result.*.RBI'                      => ['nullable', 'integer'],
            'result.*.RISP'                     => ['nullable', 'in:0,1'],
        ], [
            // 自訂回傳錯誤訊息
            'game_id' => '【game_id:比賽ID】必填且須為整數',
            'result'  => '【result:結果列表】必填且須為陣列',

            'result.*.id'                       => '【result.id:打席ID】必填且須為整數',
            'result.*.orderNo'                  => '【result.orderNo:打席順序】須為整數',
            'result.*.pitcher'                  => '【result.pitcher:投手名稱】須為字串',
            'result.*.Z00_matchupResultList_id' => '【result.Z00_matchupResultList_id:打擊結果id】須為整數',
            'result.*.Z00_location_id'          => '【result.Z00_location_id:擊球落點id】須為整數',
            'result.*.Z00_BallInPlayType_id'    => '【result.Z00_BallInPlayType_id:擊球型態id】須為整數',
            'result.*.RBI'                      => '【result.RBI:打點】須為整數',
            'result.*.RISP'                     => '【result.RISP:得點圈打擊0:否 1:是】須為整數',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = B21_batterResult::select('user_id')
                ->whereIn('id', array_column($request->input('result'), 'id'))
                ->groupBy('user_id')
                ->get();
            // return $table;

            if ($table->count() > 1 || $table[0]['user_id'] != $request->user()->id) {
                return response()->failureMessages('無修改權限', 403);
            }

            $updateLog = 0;
            DB::beginTransaction();
            foreach ($request->input('result') as $key => $result) {
                $updateArr = $result;
                unset($updateArr['id']);
                // 移除空值欄位
                foreach ($result as $k => $v) {
                    if (is_null($v) && $v == '') {
                        unset($updateArr[$k]);
                    }
                }
                // 如果有修改打擊結果或打點，則記錄需要更新打者逐場打擊紀錄
                if (strlen(trim($updateArr['Z00_matchupResultList_id'] ?? null)) > 0 || strlen(trim($updateArr['RBI'] ?? null)) > 0) {
                    $updateLog = 1;
                }
                B21_batterResult::where('id', $result['id'])->update($updateArr);
            }
            // return [$updateArr, $updateLog];

            // 如果有修改打擊結果或打點，則更新打者逐場打擊紀錄
            if ($updateLog == 1) {
                $this->B21_gameLogBatterUpdate($request->input('game_id'));
            }

            DB::commit();
            return response()->apiResponse();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->apiFail($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'resultId'   => ['required', 'array'],
            'resultId.*' => ['required', 'integer'],
        ], [
            // 自訂回傳錯誤訊息
            'resultId'   => '【resultId:結果列表】必填且須為陣列',
            'resultId.*' => '【resultId.id:打席ID】必填且須為整數',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }
        // 刪除資料
        try {
            $table = B21_batterResult::select('user_id', 'game_id')
                ->whereIn('id', $request->input('resultId'))
                ->groupBy('user_id', 'game_id')
                ->get();
            // return $table;

            $tableUserId = $table->pluck('user_id')->unique();
            $tableGameId = $table->pluck('game_id')->unique();
            if ($tableUserId->count() > 1 || $tableUserId[0] != $request->user()->id) {
                return response()->failureMessages('無修改權限', 403);
            }

            B21_batterResult::where('user_id', $request->user()->id)
                ->whereIn('id', $request->input('resultId'))
                ->delete();

            // 更新打者逐場打擊紀錄
            foreach ($tableGameId as $game_id) {
                $this->B21_gameLogBatterUpdate($game_id);
            }

            return response()->apiResponse();
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    public function B21_gameLogBatterUpdate($game_id)
    {
        $Z00_matchupResultList = Z00_matchupResultList::select('id', 'code', 'isAtBat', 'isHit', 'isOnBase', 'totalBases')
            ->get()
            ->keyBy('id');
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

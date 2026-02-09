<?php

namespace App\Http\Controllers\B00\B20;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Http\Utils\tools;
use App\Models\B00\B10\B11_games;
use App\Models\B00\B20\B21_batterResult;
use App\Models\B00\B20\B21_gameLogBatter;
use App\Models\Z00\Z00_matchupResultList;

use function PHPUnit\Framework\isInt;

class B21_battingResult extends Controller
{
    /**
     * 如果result有id則更新，沒有id則新增
     *  */
    public function updateOrCreate(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'game_id' => ['required', 'integer'],
            'result'  => ['required', 'array'],

            'result.*.id'                       => ['nullable', 'integer'],
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

            'result.*.id'                       => '【result.id:打席ID】須為整數',
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
            $B11_games = B11_games::select('user_id')->where('id', $request->input('game_id'))->first();
            if ($B11_games->user_id != $request->user()->id) {
                return response()->failureMessages('無修改權限', 403);
            }

            $table = B21_batterResult::select('id', 'user_id', 'Z00_matchupResultList_id', 'Z00_location_id', 'Z00_BallInPlayType_id')
                ->whereIn('id', array_column($request->input('result'), 'id'))
                ->get();
            // return $table;
            // 整理更新資料的userId
            $updateUserId = $table->pluck('user_id')->unique();

            if ($updateUserId->count() > 1 || ($updateUserId->count() > 0 && $updateUserId[0] != $request->user()->id)) {
                return response()->failureMessages('無修改權限', 403);
            }

            $table = $table->keyBy('id');
            $updateLog = 0;
            DB::beginTransaction();
            foreach ($request->input('result') as $key => $result) {
                if (!empty($result['id']) && isset($table[$result['id']])) {
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
                    // 如果有修改打擊結果或位置或擊球型態，則記錄需要更新打者逐場打擊紀錄
                    if (
                        strlen(trim($updateArr['Z00_matchupResultList_id'] ?? null)) > 0
                        || strlen(trim($updateArr['Z00_location_id'] ?? null)) > 0
                        || strlen(trim($updateArr['Z00_BallInPlayType_id'] ?? null)) > 0
                    ) {
                        $Z00_matchupResultList_id = $updateArr['Z00_matchupResultList_id'] ?? $table[$result['id']]['Z00_matchupResultList_id'];
                        $Z00_location_id = $updateArr['Z00_location_id'] ?? $table[$result['id']]['Z00_location_id'];
                        $Z00_BallInPlayType_id = $updateArr['Z00_BallInPlayType_id'] ?? $table[$result['id']]['Z00_BallInPlayType_id'];
                        // dd($Z00_matchupResultList_id, $Z00_location_id, $Z00_BallInPlayType_id);
                        $getDisplayName = tools::getDisplayName(
                            Z00_matchupResultList_id: $Z00_matchupResultList_id,
                            Z00_location_id: $Z00_location_id,
                            Z00_BallInPlayType_id: $Z00_BallInPlayType_id,
                        );
                        $updateArr['displayName']   = $getDisplayName['displayName'];
                        $updateArr['jaDisplayName'] = $getDisplayName['jaDisplayName'];
                    }
                    // return $updateArr;
                    B21_batterResult::where('id', $result['id'])->update($updateArr);
                } else {
                    $getDisplayName = tools::getDisplayName(
                        Z00_matchupResultList_id: $result['Z00_matchupResultList_id'],
                        Z00_location_id: $result['Z00_location_id'] ?? 0,
                        Z00_BallInPlayType_id: $result['Z00_BallInPlayType_id'] ?? 0,
                    );
                    $displayName   = $getDisplayName['displayName'];
                    $jaDisplayName = $getDisplayName['jaDisplayName'];
                    B21_batterResult::create([
                        'game_id'                     => $request->input('game_id'),
                        'user_id'                     => $request->user()->id,
                        'pitcher'                     => $result['pitcher'] ?? null,
                        'Z00_matchupResultList_id'    => $result['Z00_matchupResultList_id'],
                        'Z00_location_id'             => $result['Z00_location_id'] ?? 0,
                        'Z00_BallInPlayType_id'       => $result['Z00_BallInPlayType_id'] ?? 0,
                        'RBI'                         => $result['RBI'] ?? 0,
                        'RISP'                        => $result['RISP'],
                        'RISP'                        => $result['RISP'],
                        'displayName'                 => $displayName,
                        'jaDisplayName'               => $jaDisplayName,
                        'orderNo'                     => $result['orderNo'],
                    ]);

                    // 新增打席更新打者逐場打擊紀錄
                    $updateLog = 1;
                }
            }
            // return [$updateArr, $updateLog];

            // 如果有修改打擊結果或打點，則更新打者逐場打擊紀錄
            if ($updateLog == 1) {
                tools::B21_gameLogBatterUpdate($request->input('game_id'));
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

            B21_batterResult::whereIn('id', $request->input('resultId'))
                ->where('user_id', $request->user()->id)
                ->delete();

            // 更新打者逐場打擊紀錄
            foreach ($tableGameId as $game_id) {
                tools::B21_gameLogBatterUpdate($game_id);
            }

            return response()->apiResponse();
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }
}

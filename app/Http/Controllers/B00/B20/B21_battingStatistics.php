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

class B21_battingStatistics extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'CaseNo' => ['required', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            'CaseNo' => '【CaseNo:案件編號】必填且須為字串',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $output = [];
            //分頁清單
            // if ($request->has('page') && $request->input('page', 1) > 0) {
            //     //分頁清單
            //     $skip_paginate = (int) ($request->paginate_rows ?? $this->paginate_rows);
            //     $table  = $table->paginate($skip_paginate);
            //     $output = $table->getCollection()->transform(function ($value) {
            //         return [
            //              //
            //         ];
            //     });
            //     $output = ['data' => $output, 'total_pages' => $table->lastPage(), 'paginate' => $skip_paginate, 'total' => $table->total()];
            // } else {
            //     //不分頁清單
            //     $table = $table->get();
            //     $output = $table->transform(function ($value) {
            //         return [
            //              //
            //         ];
            //     });
            // }

            return response()->apiResponse($output);
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
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
            'R'       => ['nullable', 'integer'],
            'SB'      => ['nullable', 'integer'],
            'CS'      => ['nullable', 'integer'],
            'result'  => ['required', 'array'],

            'result.*.pitcher'                  => ['nullable', 'string'],
            'result.*.Z00_matchupResultList_id' => ['required', 'integer'],
            'result.*.Z00_location_id'          => ['nullable', 'integer'],
            'result.*.Z00_BallInPlayType_id'    => ['nullable', 'integer'],
            'result.*.RBI'                      => ['nullable', 'integer'],
            'result.*.RISP'                     => ['required', 'in:0,1'],
            'result.*.orderNo'                  => ['required', 'integer'],
        ], [
            // 自訂回傳錯誤訊息
            'game_id' => '【game_id:比賽ID】必填且須為整數',
            'R'       => '【R:得分】須為整數',
            'SB'      => '【SB:盜壘成功】須為整數',
            'CS'      => '【CS:盜壘失敗】須為整數',
            'result'  => '【result:結果列表】必填且須為陣列',

            'result.*.pitcher'                  => '【result.pitcher:投手名稱】須為字串',
            'result.*.Z00_matchupResultList_id' => '【result.Z00_matchupResultList_id:打擊結果id】必填且須為整數',
            'result.*.Z00_location_id'          => '【result.Z00_location_id:擊球落點id】須為整數',
            'result.*.Z00_BallInPlayType_id'    => '【result.Z00_BallInPlayType_id:擊球型態id】須為整數',
            'result.*.RBI'                      => '【result.RBI:打點】須為整數',
            'result.*.RISP'                     => '【result.RISP:得點圈打擊0:否 1:是】必填且須為整數',
            'result.*.orderNo'                  => '【result.orderNo:打席順序】必填且須為整數',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $Z00_matchupResultList = Z00_matchupResultList::select('id', 'code', 'isAtBat', 'isHit', 'isOnBase', 'totalBases')
                ->get()
                ->keyBy('id');
            // return $Z00_matchupResultList;

            $B21_gameLogBatterCreateArr = [
                'game_id' => $request->input('game_id'),
                'user_id' => $request->user()->id,
                'PA'      => count($request->input('result')),
                'AB'      => 0,
                'RBI'     => collect($request->result)->sum('RBI'),
                'R'       => $request->input('R', 0),
                'single'  => 0,
                'double'  => 0,
                'triple'  => 0,
                'HR'      => 0,
                'BB'      => 0,
                'IBB'     => 0,
                'HBP'     => 0,
                'SO'      => 0,
                'SH'      => 0,
                'SF'      => 0,
                'SB'      => $request->input('SB', 0),
                'CS'      => $request->input('CS', 0),
            ];

            if (B21_gameLogBatter::where('game_id', $request->input('game_id'))->exists()) {
                return response()->failureMessages('本場比賽打擊紀錄已存在，無法重複新增');
            }

            DB::beginTransaction();
            foreach ($request->input('result') as $key => $result) {
                $matchupResult = $Z00_matchupResultList[$result['Z00_matchupResultList_id']];

                // 打數
                if (((int)$matchupResult['isAtBat']) === 1) {
                    $B21_gameLogBatterCreateArr['AB'] += 1;
                }

                match ($matchupResult['id']) {
                    4 => $B21_gameLogBatterCreateArr['single'] += 1, // 1B
                    5 => $B21_gameLogBatterCreateArr['double'] += 1, // 2B
                    6 => $B21_gameLogBatterCreateArr['triple'] += 1, // 3B
                    7 => $B21_gameLogBatterCreateArr['HR'] += 1, // HR
                    8 => $B21_gameLogBatterCreateArr['SF'] += 1, // 高飛犧牲打
                    9 => $B21_gameLogBatterCreateArr['SH'] += 1, // 犧牲觸擊
                    10 => $B21_gameLogBatterCreateArr['SO'] += 1, // 三振
                    11 => $B21_gameLogBatterCreateArr['SO'] += 1, // 不死三振
                    12 => $B21_gameLogBatterCreateArr['BB'] += 1, // 四壞球
                    13 => $B21_gameLogBatterCreateArr['IBB'] += 1, // 故意四壞球
                    14 => $B21_gameLogBatterCreateArr['HBP'] += 1, // 觸身球
                };

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

            $B21_gameLogBatter = B21_gameLogBatter::create($B21_gameLogBatterCreateArr);
            $B21_gameLogBatter['battingResults'] = $B21_batterResult;

            DB::commit();
            return response()->apiResponse($B21_gameLogBatter);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->apiFail($e);
        }
    }

    /**
     * Display the specified resource.
     * 回傳該筆資料查詢資訊
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        // 參數驗證
        $validator = Validator::make([
            'id' => $id,
        ], [
            // 驗證規則
            'id' => ['required', 'integer'],

        ], [
            // 自訂回傳錯誤訊息
            'id.required' => '【id:流水號】必須指定',
            'id.integer'  => '【id:流水號】必須為整數',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $data = [];
            // $data=ActiveModel::where('id',$id)->first();
            return response()->apiResponse($data);
        } catch (\Throwable $e) {
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
    public function update(Request $request, int $game_id)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'R'         => ['nullable', 'integer'],
            'SB'        => ['nullable', 'integer'],
            'CS'        => ['nullable', 'integer'],
        ], [
            // 自訂回傳錯誤訊息
            'R'         => '【R:得分】須為整數',
            'SB'        => '【SB:盜壘成功】須為整數',
            'CS'        => '【CS:盜壘失敗】須為整數',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = B21_gameLogBatter::where('game_id', $game_id);
            if (!$table->exists()) {
                return response()->failureMessages('本場比賽打擊紀錄不存在，無法更新');
            }
            if ($request->user()->id != $table->first()->user_id) {
                return response()->failureMessages('無修改權限', 403);
            }

            // 更新資料
            $updateArr = $request->only(['R', 'SB', 'CS']);
            foreach ($updateArr as $key => $value) {
                if (is_null($value)) {
                    unset($updateArr[$key]);
                }
            }
            $table->update($updateArr);

            return response()->apiResponse();
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        // 刪除資料
        try {
            // ActiveModel::find($id)->delete();
            return response()->apiResponse();
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }
}

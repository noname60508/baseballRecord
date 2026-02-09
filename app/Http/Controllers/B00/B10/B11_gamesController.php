<?php

namespace App\Http\Controllers\B00\B10;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\B00\B20\B21_battingStatistics;

use App\Models\B00\B10\B11_games;
use App\Models\B00\B20\B21_gameLogBatter;
use Carbon\Carbon;

class B11_gamesController extends Controller
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
            'Z00_season_id'     => ['nullable', 'integer'],
            'Z00_team_id'       => ['nullable', 'integer'],
            'Z00_team_id_enemy' => ['nullable', 'integer'],
            'Z00_field_id'      => ['nullable', 'integer'],
            'gameDate'          => ['nullable', 'array', 'size:2'],
            'gameDate.*'        => ['nullable', 'date'],
            'gameResult'        => ['nullable', 'in:1,2,3'],
        ], [
            // 自訂回傳錯誤訊息
            'Z00_season_id'     => '【Z00_season_id:賽季ID】須為整數',
            'Z00_team_id'       => '【Z00_team_id:球隊ID】須為整數',
            'Z00_team_id_enemy' => '【Z00_team_id_enemy:對手球隊ID】須為整數',
            'Z00_field_id'      => '【Z00_field_id:場地ID】須為整數',
            'gameDate'          => '【gameDate:比賽日期】需為陣列，且包含兩個日期值',
            'gameDate.*'        => '【gameDate:比賽日期】陣列中的每個值皆需為日期格式',
            'gameResult'        => '【gameResult:比賽結果】僅能為1(勝)、2(敗)、3(和)',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = B11_games::select('id', 'Z00_season_id', 'Z00_team_id', 'Z00_team_id_enemy', 'Z00_field_id', 'gameDate', 'startTime', 'endTime', 'gameResult', 'homeAway', 'score', 'enemyScore', 'memo')
                ->with(['seasonName', 'teamName', 'teamNameEnemy', 'fieldName', 'batterGameLog'])
                ->where('user_id', $request->user()->id)
                ->when($request->has('Z00_season_id') && !is_null($request->input('Z00_season_id') && $request->input('Z00_season_id') != ''), function ($query) use ($request) {
                    $query->where('Z00_season_id', $request->input('Z00_season_id'));
                })
                ->when($request->has('Z00_team_id') && !is_null($request->input('Z00_team_id') && $request->input('Z00_team_id') != ''), function ($query) use ($request) {
                    $query->where('Z00_team_id', $request->input('Z00_team_id'));
                })
                ->when($request->has('Z00_team_id_enemy') && !is_null($request->input('Z00_team_id_enemy') && $request->input('Z00_team_id_enemy') != ''), function ($query) use ($request) {
                    $query->where('Z00_team_id_enemy', $request->input('Z00_team_id_enemy'));
                })
                ->when($request->has('Z00_field_id') && !is_null($request->input('Z00_field_id') && $request->input('Z00_field_id') != ''), function ($query) use ($request) {
                    $query->where('Z00_field_id', $request->input('Z00_field_id'));
                })
                ->when($request->has('gameResult') && !is_null($request->input('gameResult') && $request->input('gameResult') != ''), function ($query) use ($request) {
                    $query->where('gameResult', $request->input('gameResult'));
                })
                ->when($request->has('gameDate') && !is_null($request->gameDate[0]) && !is_null($request->gameDate[1]), function ($query) use ($request) {
                    $query->whereBetween('gameDate', $request->input('gameDate'));
                })
                ->orderBy('id', 'desc');

            // return $table->where('id', 4)->get();
            $output = [];
            //分頁清單
            if ($request->has('page') && $request->input('page', 1) > 0) {
                //分頁清單
                $skip_paginate = (int) ($request->paginate_rows ?? $this->paginate_rows);
                $table  = $table->paginate($skip_paginate);
                $output = $table->getCollection()->transform(function ($value) {
                    return [
                        'id'                => $value->id,
                        'Z00_season_id'     => $value->Z00_season_id ?? null,
                        'seasonName'        => $value->seasonName->name ?? null,
                        'Z00_team_id'       => $value->Z00_team_id ?? null,
                        'teamName'          => $value->teamName->name ?? null,
                        'Z00_team_id_enemy' => $value->Z00_team_id_enemy ?? null,
                        'teamNameEnemy'     => $value->teamNameEnemy->name ?? null,
                        'Z00_field_id'      => $value->Z00_field_id ?? null,
                        'fieldName'         => $value->fieldName->name ?? null,
                        'gameDate'          => $value->gameDate ?? null,
                        'startTime'         => $value->startTime ?? null,
                        'endTime'           => $value->endTime ?? null,
                        'homeAway'          => $value->homeAway ?? null,
                        'score'             => $value->score ?? null,
                        'enemyScore'        => $value->enemyScore ?? null,
                        'gameResult'        => $value->gameResult ?? null,
                        'memo'              => $value->memo ?? null,
                        'batterResult'      => [
                            // 打數
                            'AB' => $value->batterGameLog['AB'] ?? 0,
                            // 安打
                            'single' => $value->batterGameLog['single'] ?? 0,
                            'double' => $value->batterGameLog['double'] ?? 0,
                            'triple' => $value->batterGameLog['triple'] ?? 0,
                            'HR'     => $value->batterGameLog['HR'] ?? 0,
                            // 保送
                            'BB' => ($value->batterGameLog['BB'] ?? 0) + ($value->batterGameLog['IBB'] ?? 0),
                            // 觸身球
                            'HBP' => $value->batterGameLog['HBP'] ?? 0,
                        ],
                    ];
                });
                $output = ['data' => $output, 'total_pages' => $table->lastPage(), 'paginate' => $skip_paginate, 'total' => $table->total()];
            } else {
                //不分頁清單
                $table = $table->get();
                $output = $table->transform(function ($value) {
                    return [
                        'id'                => $value->id,
                        'Z00_season_id'     => $value->Z00_season_id ?? null,
                        'seasonName'        => $value->seasonName->name ?? null,
                        'Z00_team_id'       => $value->Z00_team_id ?? null,
                        'teamName'          => $value->teamName->name ?? null,
                        'Z00_team_id_enemy' => $value->Z00_team_id_enemy ?? null,
                        'teamNameEnemy'     => $value->teamNameEnemy->name ?? null,
                        'Z00_field_id'      => $value->Z00_field_id ?? null,
                        'fieldName'         => $value->fieldName->name ?? null,
                        'gameDate'          => $value->gameDate ?? null,
                        'startTime'         => $value->startTime ?? null,
                        'endTime'           => $value->endTime ?? null,
                        'gameResult'        => $value->gameResult ?? null,
                        'homeAway'          => $value->homeAway ?? null,
                        'score'             => $value->score ?? null,
                        'enemyScore'        => $value->enemyScore ?? null,
                        'memo'              => $value->memo ?? null,
                        'batterResult'      => [
                            // 打數
                            'AB' => $value->batterGameLog['AB'] ?? 0,
                            // 安打
                            'single' => $value->batterGameLog['single'] ?? 0,
                            'double' => $value->batterGameLog['double'] ?? 0,
                            'triple' => $value->batterGameLog['triple'] ?? 0,
                            'HR'     => $value->batterGameLog['HR'] ?? 0,
                            // 保送
                            'BB' => ($value->batterGameLog['BB'] ?? 0) + ($value->batterGameLog['IBB'] ?? 0),
                            // 觸身球
                            'HBP' => $value->batterGameLog['HBP'] ?? 0,
                        ],
                    ];
                });
            }

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
            'Z00_season_id'     => ['required', 'integer'],
            'Z00_team_id'       => ['required', 'integer'],
            'Z00_team_id_enemy' => ['nullable', 'integer'],
            'Z00_field_id'      => ['nullable', 'integer'],
            'gameDate'          => ['nullable', 'date'],
            'startTime'         => ['nullable', 'date_format:H:i'],
            'endTime'           => ['nullable', 'date_format:H:i'],
            'homeAway'          => ['nullable', 'in:1,2'],
            'score'             => ['nullable', 'integer'],
            'enemyScore'        => ['nullable', 'integer'],
            'memo'              => ['nullable', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            'Z00_season_id'     => '【Z00_season_id:賽季ID】必填且須為整數',
            'Z00_team_id'       => '【Z00_team_id:球隊ID】必填且須為整數',
            'Z00_team_id_enemy' => '【Z00_team_id_enemy:對手球隊ID】須為整數',
            'Z00_field_id'      => '【Z00_field_id:場地ID】須為整數',
            'gameDate'          => '【gameDate:比賽日期】需為日期格式',
            'startTime'         => '【startTime:比賽開始時間】需為時間格式H:i',
            'endTime'           => '【endTime:比賽結束時間】需為時間格式H:i',
            'homeAway'          => '【homeAway:先攻後攻】須為1或2 (1:先攻 2:後攻)',
            'score'             => '【score:我方得分】須為整數',
            'enemyScore'        => '【enemyScore:對方得分】須為整數',
            'memo'              => '【memo:備註】須為字串格式',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            if ($request->has('score') && $request->has('enemyScore'))
                $gameResult = $this->calculateGameResult($request->input('score'), $request->input('enemyScore'));

            $createdArr = array_merge($request->only([
                'Z00_season_id',
                'Z00_team_id',
                'Z00_team_id_enemy',
                'Z00_field_id',
                'gameDate',
                'startTime',
                'endTime',
                'homeAway',
                'score',
                'enemyScore',
                'memo',
            ]), [
                'user_id'    => $request->user()->id,
                'gameResult' => $gameResult ?? null,
            ]);
            // 建立資料
            $data = B11_games::create($createdArr);
            B21_gameLogBatter::create([
                'user_id'    => $request->user()->id,
                'game_id'    => $data->id,
            ]);

            return response()->apiResponse($data);
        } catch (\Throwable $e) {
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
            $table = B11_games::with(['seasonName', 'teamName', 'teamNameEnemy', 'fieldName',])
                ->select('id', 'Z00_season_id', 'Z00_team_id', 'Z00_team_id_enemy', 'Z00_field_id', 'gameDate', 'startTime', 'endTime', 'gameResult', 'homeAway', 'score', 'enemyScore', 'memo')
                ->where('id', $id)
                ->where('user_id', request()->user()->id)
                ->first();
            $data = [
                'id'                => $table->id,
                'Z00_season_id'     => $table->Z00_season_id ?? null,
                'seasonName'        => $table->seasonName->name ?? null,
                'Z00_team_id'       => $table->Z00_team_id ?? null,
                'teamName'          => $table->teamName->name ?? null,
                'Z00_team_id_enemy' => $table->Z00_team_id_enemy ?? null,
                'teamNameEnemy'     => $table->teamNameEnemy->name ?? null,
                'Z00_field_id'      => $table->Z00_field_id ?? null,
                'fieldName'         => $table->fieldName->name ?? null,
                'gameDate'          => $table->gameDate ?? null,
                'startTime'         => $table->startTime ? Carbon::parse($table->startTime)->format('H:i') : null,
                'endTime'           => $table->endTime ? Carbon::parse($table->endTime)->format('H:i') : null,
                'homeAway'          => $table->homeAway ?? null,
                'score'             => $table->score ?? null,
                'enemyScore'        => $table->enemyScore ?? null,
                'gameResult'        => $table->gameResult ?? null,
                'memo'              => $table->memo ?? null,
            ];

            $B21_battingStatistics = new B21_battingStatistics();
            $battingStatistics = $B21_battingStatistics->show($table->id)->getData();
            $battingStatistics = collect($battingStatistics->result)->toArray();
            // return $battingStatistics;
            if (!empty($battingStatistics)) {
                $batterResult = $battingStatistics['batterResult'];
                unset($battingStatistics['batterResult']);
            }

            $output = [
                'gameData' => $data,
                'battingStatistics' => $battingStatistics ?? [],
                'batterResult' => $batterResult ?? [],
            ];

            return response()->apiResponse($output);
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
    public function update(Request $request, int $id)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'Z00_season_id'     => ['nullable', 'integer'],
            'Z00_team_id'       => ['nullable', 'integer'],
            'Z00_team_id_enemy' => ['nullable', 'integer'],
            'Z00_field_id'      => ['nullable', 'integer'],
            'gameDate'          => ['nullable', 'date'],
            'startTime'         => ['nullable', 'date_format:H:i'],
            'endTime'           => ['nullable', 'date_format:H:i'],
            'homeAway'          => ['nullable', 'in:1,2'],
            'score'             => ['nullable', 'integer'],
            'enemyScore'        => ['nullable', 'integer'],
            'memo'              => ['nullable', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            'Z00_season_id'     => '【Z00_season_id:賽季ID】須為整數',
            'Z00_team_id'       => '【Z00_team_id:球隊ID】須為整數',
            'Z00_team_id_enemy' => '【Z00_team_id_enemy:對手球隊ID】須為整數',
            'Z00_field_id'      => '【Z00_field_id:場地ID】須為整數',
            'gameDate'          => '【gameDate:比賽日期】需為日期格式',
            'startTime'         => '【startTime:比賽開始時間】需為時間格式H:i',
            'endTime'           => '【endTime:比賽結束時間】需為時間格式H:i',
            'homeAway'          => '【homeAway:主客場】僅能為1(主場)、2(客場)',
            'score'             => '【score:我方分數】須為整數',
            'enemyScore'        => '【enemyScore:對手分數】須為整數',
            'memo'              => '【memo:備註】須為字串格式',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = B11_games::where('id', $id);
            if ($request->user()->id != $table->first()->user_id) {
                return response()->failureMessages('無修改權限', 403);
            }
            $updateArr = $request->only(['Z00_season_id', 'Z00_team_id', 'Z00_team_id_enemy', 'Z00_field_id', 'gameDate', 'startTime', 'endTime', 'homeAway', 'score', 'enemyScore', 'memo',]);

            if ($request->has('score') || $request->has('enemyScore')) {
                $score = $request->input('score', $table->first()->score);
                $enemyScore = $request->input('enemyScore', $table->first()->enemyScore);
                $gameResult = $this->calculateGameResult($score, $enemyScore);
                $updateArr['gameResult'] = $gameResult;
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
            $table = B11_games::find($id);
            if (request()->user()->id != $table->user_id) {
                return response()->failureMessages('無修改權限', 403);
            }
            $table->delete();
            return response()->apiResponse();
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    /**
     * 計算比賽結果
     * @param int $score 我方分數
     * @param int $enemyScore 對手分數
     * @return int 比賽結果 1:勝 2:敗 3:和
     */
    private function calculateGameResult($score, $enemyScore): int | null
    {
        if (is_null($score) || is_null($enemyScore)) {
            return null;
        }
        // 判斷比賽結果 <=>(比大小 左邊大於右邊為1,左邊小於右邊為-1,相等為0)
        match ($score <=> $enemyScore) {
            1 => $gameResult = 1,   // 勝
            -1 => $gameResult = 2,  // 敗
            0 => $gameResult = 3,   // 和
        };
        return (int) $gameResult;
    }
}

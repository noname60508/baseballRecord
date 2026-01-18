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
use App\Models\Z00\Z00_ballInPlayType;
use App\Models\Z00\Z00_matchupResultList;
use App\Models\Z00\Z00_positionAndLocation;

class B21_battingStatistics extends Controller
{
    public function indexValidator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'Z00_season_id'     => ['nullable', 'integer'],
            'Z00_team_id'       => ['nullable', 'integer'],
            'Z00_team_id_enemy' => ['nullable', 'integer'],
            'Z00_field_id'      => ['nullable', 'integer'],
            'gameDate'          => ['nullable', 'array', 'size:2'],
            'gameDate.*'        => ['nullable', 'date'],
            'gameResult'        => ['nullable', 'in:1,2,3'],
            'orderBy'           => ['nullable', 'array'],
            'orderBy.*'         => ['nullable', 'in:desc,asc'],
        ], [
            // 自訂回傳錯誤訊息
            'Z00_season_id'     => '【Z00_season_id:賽季ID】須為整數',
            'Z00_team_id'       => '【Z00_team_id:球隊ID】須為整數',
            'Z00_team_id_enemy' => '【Z00_team_id_enemy:對手球隊ID】須為整數',
            'Z00_field_id'      => '【Z00_field_id:場地ID】須為整數',
            'gameDate'          => '【gameDate:比賽日期】需為陣列，且包含兩個日期值',
            'gameDate.*'        => '【gameDate:比賽日期】陣列中的每個值皆需為日期格式',
            'gameResult'        => '【gameResult:比賽結果】僅能為1(勝)、2(敗)、3(和)',
            'orderBy'           => '【orderBy:排序方式】需為陣列',
            'orderBy.*'         => '【orderBy:排序方式】陣列中的每個值僅能為asc(升冪)、desc(降冪)',
        ]);
        return $validator;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 參數驗證
        $validator = $this->indexValidator($request);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = B11_games::select('id', 'Z00_season_id', 'Z00_team_id', 'Z00_team_id_enemy', 'Z00_field_id', 'gameDate', 'startTime', 'endTime', 'gameResult', 'homeAway', 'score', 'enemyScore', 'memo')
                ->with(['seasonName', 'teamName', 'teamNameEnemy', 'fieldName', 'batterGameLog', 'batterResult'])
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
                ->when($request->has('gameDate') && !is_null($request->gameDate[0]) && !is_null($request->gameDate[1]), function ($query) use ($request) {
                    $query->whereBetween('gameDate', $request->input('gameDate'));
                })
                ->when($request->has('gameResult') && !is_null($request->input('gameResult') && $request->input('gameResult') != ''), function ($query) use ($request) {
                    $query->where('gameResult', $request->input('gameResult'));
                });

            if ($request->has('orderBy')) {
                foreach ($request->input('orderBy') as $column => $direction) {
                    $table->orderBy($column, $direction);
                }
            } else {
                $table->orderBy('gameDate', 'desc')
                    ->orderBy('startTime', 'desc');
            }
            // return $table->get();

            $output = [];
            //分頁清單
            if ($request->has('page') && $request->input('page', 1) > 0) {
                //分頁清單
                $skip_paginate = (int) ($request->paginate_rows ?? $this->paginate_rows);
                $table  = $table->paginate($skip_paginate);
                $output = $table->getCollection()->transform(function ($value) {
                    foreach (collect($value->batterResult)->toArray() as $resultValue) {
                        $result[] = [
                            'id'                       => $resultValue['id'],
                            'orderNo'                  => $resultValue['orderNo'] ?? null,
                            'pitcher'                  => $resultValue['pitcher'] ?? null,
                            'Z00_matchupResultList_id' => $resultValue['Z00_matchupResultList_id'] ?? null,
                            'Z00_location_id'          => $resultValue['Z00_location_id'] ?? null,
                            'Z00_BallInPlayType_id'    => $resultValue['Z00_BallInPlayType_id'] ?? null,
                            'RBI'                      => $resultValue['RBI'] ?? null,
                            'displayName'              => $resultValue['displayName'] ?? null,
                        ];
                    }
                    return [
                        'gameId' => $value->id,
                        'PA'     => $value->batterGameLog['PA'] ?? 0,
                        'AB'     => $value->batterGameLog['AB'] ?? 0,
                        'RBI'    => $value->batterGameLog['RBI'] ?? 0,
                        'R'      => $value->batterGameLog['R'] ?? 0,
                        'single' => $value->batterGameLog['single'] ?? 0,
                        'double' => $value->batterGameLog['double'] ?? 0,
                        'triple' => $value->batterGameLog['triple'] ?? 0,
                        'HR'     => $value->batterGameLog['HR'] ?? 0,
                        'BB'     => $value->batterGameLog['BB'] ?? 0,
                        'IBB'    => $value->batterGameLog['IBB'] ?? 0,
                        'HBP'    => $value->batterGameLog['HBP'] ?? 0,
                        'SO'     => $value->batterGameLog['SO'] ?? 0,
                        'SH'     => $value->batterGameLog['SH'] ?? 0,
                        'SF'     => $value->batterGameLog['SF'] ?? 0,
                        'SB'     => $value->batterGameLog['SB'] ?? 0,
                        'CS'     => $value->batterGameLog['CS'] ?? 0,

                        'result' => $result ?? [],
                    ];
                });
                $output = ['data' => $output, 'total_pages' => $table->lastPage(), 'paginate' => $skip_paginate, 'total' => $table->total()];
            } else {
                //不分頁清單
                $table = $table->get();
                $output = $table->transform(function ($value) {
                    foreach (collect($value->batterResult)->toArray() as $resultValue) {
                        $result[] = [
                            'id'                       => $resultValue['id'],
                            'orderNo'                  => $resultValue['orderNo'] ?? null,
                            'pitcher'                  => $resultValue['pitcher'] ?? null,
                            'Z00_matchupResultList_id' => $resultValue['Z00_matchupResultList_id'] ?? null,
                            'Z00_location_id'          => $resultValue['Z00_location_id'] ?? null,
                            'Z00_BallInPlayType_id'    => $resultValue['Z00_BallInPlayType_id'] ?? null,
                            'RBI'                      => $resultValue['RBI'] ?? null,
                            'displayName'              => $resultValue['displayName'] ?? null,
                        ];
                    }
                    return [
                        'gameId' => $value->id,
                        'PA'     => $value->batterGameLog['PA'] ?? 0,
                        'AB'     => $value->batterGameLog['AB'] ?? 0,
                        'RBI'    => $value->batterGameLog['RBI'] ?? 0,
                        'R'      => $value->batterGameLog['R'] ?? 0,
                        'single' => $value->batterGameLog['single'] ?? 0,
                        'double' => $value->batterGameLog['double'] ?? 0,
                        'triple' => $value->batterGameLog['triple'] ?? 0,
                        'HR'     => $value->batterGameLog['HR'] ?? 0,
                        'BB'     => $value->batterGameLog['BB'] ?? 0,
                        'IBB'    => $value->batterGameLog['IBB'] ?? 0,
                        'HBP'    => $value->batterGameLog['HBP'] ?? 0,
                        'SO'     => $value->batterGameLog['SO'] ?? 0,
                        'SH'     => $value->batterGameLog['SH'] ?? 0,
                        'SF'     => $value->batterGameLog['SF'] ?? 0,
                        'SB'     => $value->batterGameLog['SB'] ?? 0,
                        'CS'     => $value->batterGameLog['CS'] ?? 0,

                        'result' => $result ?? [],
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
            if (B21_gameLogBatter::where('game_id', $request->input('game_id'))->exists()) {
                return response()->failureMessages('本場比賽打擊紀錄已存在，無法重複新增');
            }

            DB::beginTransaction();
            $B21_gameLogBatter = B21_gameLogBatter::create([
                'game_id' => $request->input('game_id'),
                'user_id' => $request->user()->id,
                'PA'      => 0,
                'AB'      => 0,
                'RBI'     => 0,
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
            ]);

            foreach ($request->input('result') as $key => $result) {
                $displayName = tools::getDisplayName(
                    Z00_matchupResultList_id: $result['Z00_matchupResultList_id'],
                    Z00_location_id: $result['Z00_location_id'] ?? 0,
                    Z00_BallInPlayType_id: $result['Z00_BallInPlayType_id'] ?? 0,
                );
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
                    'displayName'                 => $displayName,
                ]);
            }

            tools::B21_gameLogBatterUpdate($request->input('game_id'));

            DB::commit();
            return response()->apiResponse();
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
    public function show(int $game_id)
    {
        try {
            $table = B21_gameLogBatter::where('game_id', $game_id)
                ->select('id', 'game_id', 'user_id', 'PA', 'AB', 'RBI', 'R', 'single', 'double', 'triple', 'HR', 'BB', 'IBB', 'HBP', 'SO', 'SH', 'SF', 'SB', 'CS')
                ->with(['batterResult'])
                ->first();

            if (empty($table)) {
                return response()->apiResponse();
            }

            if (!empty($table['batterResult'])) {
                foreach ($table['batterResult'] as $key => $value) {
                    $batterResult[] = [
                        'id' => $value['id'],
                        // 'game_id' => $value['game_id'],
                        'orderNo' => $value['orderNo'],
                        'pitcher' => $value['pitcher'],
                        'Z00_matchupResultList_id' => $value['Z00_matchupResultList_id'],
                        'Z00_location_id' => $value['Z00_location_id'],
                        'Z00_BallInPlayType_id' => $value['Z00_BallInPlayType_id'],
                        'RBI' => $value['RBI'],
                        'displayName' => $value['displayName'],
                        'RISP' => $value['RISP'],
                    ];
                }
            }

            $data = [
                // 'id' => $table['id'],
                'game_id' => $table['game_id'],
                'user_id' => $table['user_id'],
                'PA' => $table['PA'],
                'AB' => $table['AB'],
                'RBI' => $table['RBI'],
                'R' => $table['R'],
                'single' => $table['single'],
                'double' => $table['double'],
                'triple' => $table['triple'],
                'HR' => $table['HR'],
                'BB' => $table['BB'],
                'IBB' => $table['IBB'],
                'HBP' => $table['HBP'],
                'SO' => $table['SO'],
                'SH' => $table['SH'],
                'SF' => $table['SF'],
                'SB' => $table['SB'],
                'CS' => $table['CS'],
                'batterResult' => $batterResult ?? [],
            ];
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
                // 如果是空字串就移除，不更新
                if (strlen(trim($value)) == 0) {
                    unset($updateArr[$key]);
                }
            }
            $table->update($updateArr);

            return response()->apiResponse();
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    public function dataStatistics(Request $request)
    {
        // 參數驗證
        $validator = $this->indexValidator($request);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $B21_gameLogBatterStatistics = $this->B21_gameLogBatterStatistics($request);
            $B21_batterResultStatistics = $this->B21_batterResultStatistics($request);
            $statistics = array_merge($B21_gameLogBatterStatistics['statistics'], [
                'RISP_AVG' => $B21_batterResultStatistics['RISP_AVG'],
            ]);
            // return [$B21_gameLogBatterStatistics, $B21_batterResultStatistics];

            $output = [
                'total' => $B21_gameLogBatterStatistics['total'],
                'statistics' => $statistics,
                'BIP' => $B21_batterResultStatistics['BIP'],
                'distribution' => $B21_batterResultStatistics['distribution'],
            ];

            return response()->apiResponse($output);
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    public function B21_gameLogBatterStatistics(Request $request)
    {
        $table = B21_gameLogBatter::where('B11_games.user_id', $request->user()->id)
            ->selectRaw("
                    count(1) as gamesPlayed,
                    sum(PA) as PA,
                    sum(AB) as AB,
                    sum(RBI) as RBI,
                    sum(R) as R,
                    sum(single) as single,
                    sum(`double`) as `double`,
                    sum(triple) as triple,
                    sum(HR) as HR,
                    sum(BB) as BB,
                    sum(IBB) as IBB,
                    sum(HBP) as HBP,
                    sum(SO) as SO,
                    sum(SH) as SH,
                    sum(SF) as SF,
                    sum(SB) as SB,
                    sum(CS) as CS
                ")
            ->leftJoin('B11_games', 'B21_gameLogBatter.game_id', '=', 'B11_games.id')
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
            ->when($request->has('gameDate') && !is_null($request->gameDate[0]) && !is_null($request->gameDate[1]), function ($query) use ($request) {
                $query->whereBetween('gameDate', $request->input('gameDate'));
            })
            ->when($request->has('gameResult') && !is_null($request->input('gameResult') && $request->input('gameResult') != ''), function ($query) use ($request) {
                $query->where('gameResult', $request->input('gameResult'));
            })
            ->whereNull('B11_games.deleted_at')
            ->first();

        $output['total'] = [
            'gamesPlayed' => (int)$table->gamesPlayed ?? 0,
            'PA'          => (int)$table->PA ?? 0,
            'AB'          => (int)$table->AB ?? 0,
            'RBI'         => (int)$table->RBI ?? 0,
            'R'           => (int)$table->R ?? 0,
            'single'      => (int)$table->single ?? 0,
            'double'      => (int)$table->double ?? 0,
            'triple'      => (int)$table->triple ?? 0,
            'HR'          => (int)$table->HR ?? 0,
            'BB'          => (int)$table->BB ?? 0,
            'IBB'         => (int)$table->IBB ?? 0,
            'HBP'         => (int)$table->HBP ?? 0,
            'SO'          => (int)$table->SO ?? 0,
            'SH'          => (int)$table->SH ?? 0,
            'SF'          => (int)$table->SF ?? 0,
            'SB'          => (int)$table->SB ?? 0,
            'CS'          => (int)$table->CS ?? 0,
        ];

        $hit = ($table->single ?? 0) + ($table->double ?? 0) + ($table->triple ?? 0) + ($table->HR ?? 0);
        $avg = $table->PA > 0 ? round($hit / $table->AB, 3) : 0;
        $OBP = $table->PA > 0 ? round(($hit + $table->BB + $table->IBB + $table->HBP) / ($table->AB + $table->BB + $table->IBB + $table->HBP + $table->SF), 3) : 0;
        $SLG = $table->PA > 0 ? round(($table->single + (2 * $table->double) + (3 * $table->triple) + (4 * $table->HR)) / $table->AB, 3) : 0;
        $OPS = $table->PA > 0 ? round($OBP + $SLG, 3) : 0;
        $output['statistics'] = [
            'AVG' => $avg,
            'OBP' => $OBP,
            'SLG' => $SLG,
            'OPS' => $OPS,
            'KPercentage' => $table->PA > 0 ? round($table->SO / $table->PA * 100, 3)  : 0,
            'BBPercentage' => $table->PA > 0 ? round(($table->BB + $table->IBB) / $table->PA * 100, 3) : 0,
            'SBPercentage' => ($table->SB + $table->CS) > 0 ? round($table->SB / ($table->SB + $table->CS) * 100, 3) : 0,
        ];

        return $output;
    }

    public function B21_batterResultStatistics(Request $request)
    {
        $table = B21_batterResult::where('B11_games.user_id', $request->user()->id)
            ->leftJoin('B11_games', 'B11_games.id', '=', 'B21_batterResult.game_id')
            ->leftJoin('Z00_matchupResultList', 'B21_batterResult.Z00_matchupResultList_id', '=', 'Z00_matchupResultList.id')
            ->leftJoin('Z00_positionAndLocation', 'B21_batterResult.Z00_location_id', '=', 'Z00_positionAndLocation.id')
            ->select('Z00_matchupResultList_id', 'Z00_location_id', 'Z00_BallInPlayType_id', 'RISP', 'isAtBat', 'isHit', 'isOnBase', 'totalBases', 'ballInPlay', 'isFairBall')
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
            ->when($request->has('gameDate') && !is_null($request->gameDate[0]) && !is_null($request->gameDate[1]), function ($query) use ($request) {
                $query->whereBetween('gameDate', $request->input('gameDate'));
            })
            ->when($request->has('gameResult') && !is_null($request->input('gameResult') && $request->input('gameResult') != ''), function ($query) use ($request) {
                $query->where('gameResult', $request->input('gameResult'));
            })
            ->whereNull('B11_games.deleted_at')
            ->get();

        // return $table;

        // 得點圈打擊率預設值
        $RISP_AB = 0;
        $RISP_hit = 0;

        // 打擊型態BIP分布預設值
        foreach (Z00_ballInPlayType::select('id', 'code', 'name')->get() as $value) {
            $ballInPlay[$value->id] = [
                'code' => $value->code,
                'name' => $value->name,
                'sum' => 0,
            ];
        };
        $BIP_total = 0;

        // 擊球分布預設值
        foreach (Z00_positionAndLocation::select('id', 'code', 'name')->where('isFairBall', 1)->get() as $value) {
            $location[$value->id] = [
                'code' => $value->code,
                'name' => $value->name,
                'hit' => 0,
                'sum' => 0,
            ];
        };
        $location_total = 0;

        foreach ($table as $key => $value) {
            // 計算得點圈打擊率
            if ($value['RISP'] == 1) {
                $value['isAtBat'] == 1 ? $RISP_AB++ : '';
                $value['isHit'] == 1 ? $RISP_hit++ : '';
            }

            // 計算打擊型態BIP分布(全壘打不列入計算) && 計算擊球分布
            if ($value['ballInPlay'] == 1 && $value['isFairBall'] == 1 && $value['Z00_matchupResultList_id'] != 7) {
                // 打擊型態
                $BIP_total++;
                $ballInPlay[$value['Z00_BallInPlayType_id']]['sum']++;

                // 擊球分布
                $location_total++;
                $location[$value['Z00_location_id']]['sum']++;
                $value['isHit'] == 1 ? $location[$value['Z00_location_id']]['hit']++ : '';
            }
        }

        // 計算打擊型態百分比
        foreach ($ballInPlay as $key => $value) {
            $BIP[$value['code']] = [
                'name' => $value['name'],
                'sum' => $value['sum'],
                'percentage' => $BIP_total > 0 ? round($value['sum'] / $BIP_total * 100, 2) : 0,
            ];
        }

        // 計算擊球分布百分比
        foreach ($location as $key => $value) {
            $distribution[$value['code']] = [
                'name' => $value['name'],
                'hit' => $value['hit'],
                'sum' => $value['sum'],
                'percentage' => $location_total > 0 ? round($value['sum'] / $location_total * 100, 2) : 0,
                'locationAVG' => $value['sum'] > 0 ? round($value['hit'] / $value['sum'], 3) : 0,
            ];
        }

        $output = [
            'RISP_AVG' => $RISP_AB > 0 ? round($RISP_hit / $RISP_AB, 3) : 0,
            'BIP' => $BIP ?? [],
            'distribution' => $distribution ?? [],
        ];

        return $output;
    }
}

<?php

namespace App\Http\Controllers\B00\B10;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Models\B00\B10\B11_games;

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
            'result'            => ['nullable', 'in:1,2,3'],
        ], [
            // 自訂回傳錯誤訊息
            'Z00_season_id'     => '【Z00_season_id:賽季ID】須為整數',
            'Z00_team_id'       => '【Z00_team_id:球隊ID】須為整數',
            'Z00_team_id_enemy' => '【Z00_team_id_enemy:對手球隊ID】須為整數',
            'Z00_field_id'      => '【Z00_field_id:場地ID】須為整數',
            'gameDate'          => '【gameDate:比賽日期】需為陣列，且包含兩個日期值',
            'gameDate.*'        => '【gameDate:比賽日期】陣列中的每個值皆需為日期格式',
            'result'            => '【result:比賽結果】僅能為1(勝)、2(敗)、3(和)',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = B11_games::select('id', 'Z00_season_id', 'Z00_team_id', 'Z00_team_id_enemy', 'Z00_field_id', 'gameDate', 'startTime', 'endTime', 'result', 'memo')
                ->with(['seasonName', 'teamName', 'teamNameEnemy', 'fieldName'])
                ->where('user_id', $request->user()->id)
                ->when($request->has('Z00_season_id') && !is_null($request->input('Z00_season_id')), function ($query) use ($request) {
                    $query->where('Z00_season_id', $request->input('Z00_season_id'));
                })
                ->when($request->has('Z00_team_id') && !is_null($request->input('Z00_team_id')), function ($query) use ($request) {
                    $query->where('Z00_team_id', $request->input('Z00_team_id'));
                })
                ->when($request->has('Z00_team_id_enemy') && !is_null($request->input('Z00_team_id_enemy')), function ($query) use ($request) {
                    $query->where('Z00_team_id_enemy', $request->input('Z00_team_id_enemy'));
                })
                ->when($request->has('Z00_field_id') && !is_null($request->input('Z00_field_id')), function ($query) use ($request) {
                    $query->where('Z00_field_id', $request->input('Z00_field_id'));
                })
                ->when($request->has('gameDate') && !is_null($request->gameDate[0]) && !is_null($request->gameDate[1]), function ($query) use ($request) {
                    $query->whereBetween('gameDate', $request->input('gameDate'));
                })
                ->when($request->has('result') && !is_null($request->input('result')), function ($query) use ($request) {
                    $query->where('result', $request->input('result'));
                });
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
                        'result'            => $value->result ?? null,
                        'memo'              => $value->memo ?? null,
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
                        'result'            => $value->result ?? null,
                        'memo'              => $value->memo ?? null,
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
            'result'            => ['nullable', 'in:1,2,3'],
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
            'result'            => '【result:比賽結果】僅能為1(勝)、2(敗)、3(和)',
            'memo'              => '【memo:備註】須為字串格式',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $createdArr = array_merge($request->only([
                'Z00_season_id',
                'Z00_team_id',
                'Z00_team_id_enemy',
                'Z00_field_id',
                'gameDate',
                'startTime',
                'endTime',
                'result',
                'memo',
            ]), [
                'user_id' => $request->user()->id,
            ]);
            // 建立資料
            $data = B11_games::create($createdArr);

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
                ->select('id', 'Z00_season_id', 'Z00_team_id', 'Z00_team_id_enemy', 'Z00_field_id', 'gameDate', 'startTime', 'endTime', 'result', 'memo')
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
                'startTime'         => $table->startTime ?? null,
                'endTime'           => $table->endTime ?? null,
                'result'            => $table->result ?? null,
                'memo'              => $table->memo ?? null,
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
    public function update(Request $request, int $id)
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
            'result'            => ['nullable', 'in:1,2,3'],
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
            'result'            => '【result:比賽結果】僅能為1(勝)、2(敗)、3(和)',
            'memo'              => '【memo:備註】須為字串格式',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = B11_games::where('id', $id);
            if ($request->user()->id != $table->first()->user_id) {
                return response()->failureMessages('無修改權限');
            }
            $updateArr = $request->only(['Z00_season_id', 'Z00_team_id', 'Z00_team_id_enemy', 'Z00_field_id', 'gameDate', 'startTime', 'endTime', 'result', 'memo',]);

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
            $table = B11_games::find($id);
            if (request()->user()->id != $table->first()->user_id) {
                return response()->failureMessages('無修改權限');
            }
            $table->delete();
            return response()->apiResponse();
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }
}

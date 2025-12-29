<?php

namespace App\Http\Controllers\Z00;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Z00\Z00_fields;

class Z00_fieldsController extends Controller
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
            'name' => ['nullable', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            'name' => '【name:場地名稱】須為字串',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = Z00_fields::select('id', 'name')
                ->where('user_id', $request->user()->id)
                ->when($request->has('name') && !is_null($request->input('name')), function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->input('name') . '%');
                });
            $output = [];
            //分頁清單
            if ($request->has('page') && $request->input('page', 1) > 0) {
                //分頁清單
                $skip_paginate = (int) ($request->paginate_rows ?? $this->paginate_rows);
                $table  = $table->paginate($skip_paginate);
                $output = $table->getCollection()->transform(function ($value) {
                    return [
                        'id'   => $value->id,
                        'name' => $value->name,
                    ];
                });
                $output = ['data' => $output, 'total_pages' => $table->lastPage(), 'paginate' => $skip_paginate, 'total' => $table->total()];
            } else {
                //不分頁清單
                $table = $table->get();
                $output = $table->transform(function ($value) {
                    return [
                        'id'   => $value->id,
                        'name' => $value->name,
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
            'name' => ['required', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            'name' => '【name:場地名稱】必填且須為字串',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $data = [
                'name'    => $request->input('name'),
                'user_id' => $request->user()->id,
            ];
            $table = Z00_fields::create($data);

            return response()->apiResponse($table);
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
            'name' => ['nullable', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            'name' => '【name:場地名稱】須為字串',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = Z00_fields::where('id', $id);
            if ($request->user()->id != $table->first()->user_id) {
                return response()->failureMessages('無修改權限');
            }
            if ($request->has('name')) {
                $table->update(['name' => $request->input('name')]);
            }

            return response()->apiResponse(Z00_fields::find($id));
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
            $table = Z00_fields::find($id);
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

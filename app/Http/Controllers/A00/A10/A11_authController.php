<?php

namespace App\Http\Controllers\A00\A10;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class A11_authController extends Controller
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
            // 'CaseNo' => ['required', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            // 'CaseNo' => '【CaseNo:案件編號】必填且須為字串',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $output = [];
            //分頁清單
            // $skip_paginate = (int) ($request->paginate_rows ?? $this->paginate_rows);
            // $table  = $table->paginate($skip_paginate);
            // $output = $table->getCollection()->transform(function ($value) {
            //     return [
            //     ];
            // });

            // $output = ['data' => $output, 'total_pages' => $table->lastPage(), 'paginate' => $skip_paginate, 'total' => $table->total()];
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
            // 'CaseNo' => ['required', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            // 'CaseNo' => '【CaseNo:案件編號】必填且須為字串',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            // 回傳資料
            $data = [];
            // $data=ActiveModel::create($validator);
            return response()->apiResponse([]);
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
    public function update(Request $request, int $id)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            // 'CaseNo' => ['required', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            // 'CaseNo' => '【CaseNo:案件編號】必填且須為字串',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            // $ActiveModel=ActiveModel::where('id',$id);
            // $updateArr = $request->only([]);
            // $ActiveModel->update($updateArr);

            return response()->apiResponse([]);
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
            return response()->apiResponse([]);
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }
}

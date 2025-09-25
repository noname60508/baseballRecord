<?php

namespace App\Http\Controllers\A00\A10;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class A11_authController extends Controller
{
    public function register(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'account'  => ['required', 'string'],
            'password' => ['required', 'string'],
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email'],
        ], [
            // 自訂回傳錯誤訊息
            'account'  => '【帳號】必填且須為字串',
            'password' => '【密碼】必填且須為字串',
            'name'     => '【使用者名稱】必填且須為字串',
            'email'    => '【信箱】必填且須為信箱格式',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            // 檢查是否已存在相同帳號
            $existingUser = User::where('account', $request->account)->exists();
            if ($existingUser) {
                return response()->failureMessages(['account' => '帳號已存在']);
            }
            // 檢查信箱是否已存在
            $existingEmail = User::where('email', $request->email)->exists();
            if ($existingEmail) {
                return response()->failureMessages(['email' => '信箱已存在']);
            }

            // 建立新使用者
            $user = User::create([
                'account'  => $request->account,
                'password' => bcrypt($request->password),
                'name'     => $request->name,
                'email'    => $request->email,
            ]);

            return response()->apiResponse($user);
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    public function login(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'account'  => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            // 自訂回傳錯誤訊息
            'account'  => '【帳號】必填且須為字串',
            'password' => '【密碼】必填且須為字串',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $user = User::where('account', $request->account)->first();
            if (!$user) {
                return response()->failureMessages(['查無此帳號']);
            }
            if ($user && $user->isBan) {
                return response()->failureMessages(['帳號已被停用，請洽管理員']);
            }
            if ($user && $user->failCooldown && $this->now->lessThan($user->failCooldown)) {
                $diff = (int) $this->now->diffInMinutes($user->failCooldown);
                return response()->failureMessages(['帳號已鎖定，請' . $diff . '分鐘後再試']);
            }
            // 帳號或密碼錯誤
            if (!$user || !password_verify($request->password, $user->password)) {
                $faileCount = ($user->failCount ?? 0) + 1;

                if ($faileCount >= 5) {
                    // 鎖定帳號
                    User::where('id', $user->id)->update([
                        'failCount'    => $faileCount,
                        'failCooldown' => $this->now->addMinutes(10),
                    ]);
                    return response()->failureMessages(['帳號已鎖定，請10分鐘後再試']);
                }

                User::where('id', $user->id)->update([
                    'failCount' => $faileCount,
                ]);
                return response()->failureMessages(['帳號或密碼錯誤']);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            User::where('id', $user->id)->update([
                'lastLoginAt'  => $this->now,
                'lastLoginIp'  => $request->ip(),
                'failCount'    => 0,
                'failCooldown' => null,
            ]);

            $output = [
                'id'      => $user->id,
                'account' => $user->account,
                'name'    => $user->name,
                'email'   => $user->email,
                'isBan'   => $user->isBan,
            ];
            return response()->apiResponse($output, $token);
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $request->user()->currentAccessToken();
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'account' => ['nullable', 'string'],
            'name'    => ['nullable', 'string'],
            'email'   => ['nullable', 'string'],
            'isBan'   => ['nullable', 'in:0,1'],
        ], [
            // 自訂回傳錯誤訊息
            'account' => '【帳號】須為字串',
            'name'    => '【使用者名稱】須為字串',
            'email'   => '【信箱】須為字串',
            'isBan'   => '【是否停用】須為0或1',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $table = User::select('id', 'account', 'name', 'email', 'isBan');

            foreach ($request->only(['account', 'name', 'email', 'isBan']) as $key => $value) {
                if ($value === '') continue;

                if (!is_null($value) && in_array($key, ['account', 'name', 'email'])) {
                    $table->where($key, 'like', '%' . trim($value) . '%');
                }
                if (!is_null($value) && in_array($key, ['isBan'])) {
                    $table->where($key, $value);
                }
            }

            $output = [];
            //分頁清單
            $skip_paginate = (int) ($request->paginate_rows ?? $this->paginate_rows);
            $table  = $table->paginate($skip_paginate);
            $output = $table->getCollection()->transform(function ($value) {
                return [
                    'id'      => $value->id,
                    'account' => $value->account ?? null,
                    'name'    => $value->name ?? null,
                    'email'   => $value->email ?? null,
                    'isBan'   => $value->isBan ?? null,
                ];
            });

            $output = ['data' => $output, 'total_pages' => $table->lastPage(), 'paginate' => $skip_paginate, 'total' => $table->total()];
            return response()->apiResponse($output);
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
            $data = User::where('id', $id)->first();

            $output = [
                'id'      => $data->id,
                'account' => $data->account ?? null,
                'name'    => $data->name ?? null,
                'email'   => $data->email ?? null,
                'email_verified_at' => empty($data->email_verified_at) ? 0 : 1,
                'isBan'   => $data->isBan ?? null,
                'lastLoginAt' => !empty($data->lastLoginAt) ? Carbon::parse($data->lastLoginAt)->format('Y-m-d H:i:s') : null,
                'lastLoginIp' => $data->lastLoginIp ?? null,
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
            'password' => ['nullable', 'string'],
            'name'     => ['nullable', 'string'],
            'email'    => ['nullable', 'email'],
            'isBan'    => ['nullable', 'in:0,1'],
        ], [
            // 自訂回傳錯誤訊息
            'password' => '【密碼】須為字串',
            'name'     => '【使用者名稱】須為字串',
            'email'    => '【信箱】須為信箱格式',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator->errors());
        }

        try {
            $updateArr = [];
            $user = User::where('id', $id);

            foreach ($request->only(['password', 'name', 'email', 'isBan']) as $key => $value) {
                if ($value === '') continue;

                if ($key === 'password') {
                    $updateArr['password'] = bcrypt($value);
                }

                if (!is_null($value) && in_array($key, ['name', 'email'])) {
                    $updateArr[$key] = $value;
                }
            }

            $user->update($updateArr);

            return response()->apiResponse($user->first());
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
            User::find($id)->delete();
            return response()->apiResponse('刪除成功');
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }
}

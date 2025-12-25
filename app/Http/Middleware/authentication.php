<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Laravel\Sanctum\PersonalAccessToken;

class authentication
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // 檢查是否有 Authorization header
            $bearerToken = $request->bearerToken();

            if (!$bearerToken) {
                return $this->unauthorizedResponse('缺少認證 Token');
            }

            // 使用 Sanctum 的 PersonalAccessToken 來查找 token
            $accessToken = PersonalAccessToken::findToken($bearerToken);

            if (!$accessToken) {
                return $this->unauthorizedResponse('Token 無效或已過期');
            }

            // 取得關聯的使用者
            $user = $accessToken->tokenable;

            if (!$user) {
                return $this->unauthorizedResponse('使用者不存在');
            }

            // 檢查 token 是否有設定 abilities (可選)
            // if (!$accessToken->can('*')) {
            //     return $this->unauthorizedResponse('Token 權限不足');
            // }

            // 將使用者和 token 設定到請求中
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            // 設定當前的 access token,這樣 currentAccessToken() 才不會是 null
            $user->withAccessToken($accessToken);

            return $next($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorizedResponse('認證失敗');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('認證過程發生錯誤: '/*  . $e->getMessage() */);
        }
    }

    private function unauthorizedResponse(string $message)
    {
        return response()->json([
            'message' => 'token驗證失敗',
            'result'  => $message,
        ], 401);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // 檢查是否有 Authorization header
            if (!$request->bearerToken()) {
                return $this->unauthorizedResponse('缺少認證 Token');
            }

            // 使用 Sanctum 認證
            $user = auth('sanctum')->user();

            if (!$user) {
                return $this->unauthorizedResponse('Token 無效或已過期');
            }

            // 將使用者設定到請求中
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorizedResponse('認證失敗');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('認證過程發生錯誤');
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

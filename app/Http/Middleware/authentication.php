<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Models\User;

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

            $timeZone = env('APP_TIMEZONE', 'Asia/Tokyo');
            $now = Carbon::now($timeZone);
            // token延展後的時間
            $tokenExpireAt = (clone $now)->addSeconds(config('envDefault.tokenExpire'));
            $expires_at = $accessToken->expires_at ? Carbon::parse($accessToken->expires_at, $timeZone) : null;
            if (!$expires_at) {
                return $this->unauthorizedResponse('Token 無效或已過期');
            }

            // 如果沒有token或token過期超過一個月
            if ($now > (clone $expires_at)->addMonth()) {
                return $this->unauthorizedResponse('Token 無效或已過期');
            }
            // 如果token過期 && 未過期超過一個月
            elseif (
                $now >= $expires_at
                && $now <= (clone $expires_at)->addMonth()
            ) {
                // 生成新的token
                $bearerToken = User::where('id', $accessToken->tokenable_id)->first()
                    ->createToken(
                        'auth_token',
                        $accessToken->abilities,
                        $tokenExpireAt
                    )
                    ->plainTextToken;

                // 刪除舊的token
                if ($accessToken)
                    $accessToken->delete();

                // 取新的token資料
                $accessToken = PersonalAccessToken::findToken($bearerToken);
            }
            // 如果在期限內延長token的有效期
            else {
                $accessToken->forceFill(['expires_at' => $tokenExpireAt])->save();
            }
            // Log::debug('now: ' . $now . ' expires_at: ' . $expires_at . ' tokenExpireAt: ' . $tokenExpireAt);

            // 取得關聯的使用者
            $user = $accessToken->tokenable;

            if (!$user) {
                return $this->unauthorizedResponse('使用者不存在');
            }

            // 檢查 token 是否有設定 abilities (可選)
            // if (!$accessToken->can('*')) {
            //     return $this->unauthorizedResponse('Token 權限不足');
            // }

            // 設定當前的使用者
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            auth()->setUser($user);

            // 設定當前的 access token,這樣 currentAccessToken() 才不會是 null
            $user->withAccessToken($accessToken);

            return $next($request)->header('Authorization', 'Bearer ' . $bearerToken);
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

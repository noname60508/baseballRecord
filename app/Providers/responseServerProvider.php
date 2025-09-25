<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\Routing\ResponseFactory;

class responseServerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // api回傳資料
        Response::macro('apiResponse', function ($data = [], $token = null) {
            if (empty($token)) {
                return Response::json(responseServerProvider::apiResponse(200, $data), 200);
            } else {
                return Response::json(responseServerProvider::apiResponse(200, $data), 200)
                    ->header('Authorization', $token);
            }
        });

        // bug回傳資料
        Response::macro('apiFail', function ($e, $code = 500) {
            report($e);
            $message = env('APP_DEBUG') ? $e->getMessage() : '';
            return Response::json(responseServerProvider::apiResponse(500, $message), $code);
        });

        // 驗證失敗回傳
        Response::macro('failureMessages', function ($message, $code = 400) {
            return Response::json(responseServerProvider::apiResponse(400, $message), $code);
        });
    }

    public static function apiResponse($code = 0, $data = [])
    {
        return [
            // 'code'    => (string) $code,
            'message' => trans('errorCode.' . $code),
            'result'  => $data,
        ];
    }
}

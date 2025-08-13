<?php

namespace App\Http\Utils;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\sidewalk\sidewalk_model;
use App\Models\road\road_model;
use App\Models\indicator\indicator_model;
use App\Models\missing\missing_model;
use App\Models\missingRoad\missingRoad_model;

class map
{
    // 座標整理成sql格式
    public function coordinateTidy(array $coordinate): string
    {
        foreach ($coordinate as $value) {
            $arr[] = $value['X'] . ' ' . $value['Y'];
        }
        // return $arr;

        // 起點要跟終點一樣
        array_push($arr, $arr[0]);
        $coordinate = implode(',', $arr);
        return $coordinate;
    }

    /**
     * 地圖搜尋-基礎資料查詢
     */
    public function mapSelect(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'coordinate'   => 'required|array|min:3',
            'coordinate.*' => 'required|size:2',
        ], [
            // 自訂回傳錯誤訊息
            // !!!!!!!!!!!!!需順時針給!!!!!!!!!!!!!
            'coordinate'            => '【座標】必填且需為陣列及需大於三組',
            'coordinate.*.required' => '【座標】必填',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator);
        }

        try {
            $selectArr = [DB::raw('geom.STAsText() as STAsText'), 'qgs_fid'];
            if (!empty($request->coordinate)) {
                $coordinate = $this->coordinateTidy($request->coordinate);
                // return $coordinate;

                $whereArr[] = ["geom.STIntersects(geometry::STGeomFromText('POLYGON(({$coordinate}))', 3826))", '=', 1];
            }
            $sidewalk = sidewalk_model::sidewalk_unionAll_v2($selectArr, $whereArr);
            $road = road_model::road_unionAll_v2($selectArr, $whereArr);
            $table = $sidewalk
                ->unionAll($road);
            // return $table->get();

            return $table;
            // return response()->apiResponse(101, $output);
        } catch (\Throwable $e) {
            return response()->apiFail(100, $e);
        }
    }

    /**
     * 地圖搜尋-指標資料
     */
    public function mapSelectIndicator(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'coordinate'   => 'required|array|min:3',
            'coordinate.*' => 'required|size:2',
        ], [
            // 自訂回傳錯誤訊息
            // !!!!!!!!!!!!!需順時針給!!!!!!!!!!!!!
            'coordinate'            => '【座標】必填且需為陣列及需大於三組',
            'coordinate.*.required' => '【座標】必填',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator);
        }

        try {
            $selectArr = [DB::raw('geom.STAsText() as STAsText'), 'qgs_fid'];
            if (!empty($request->coordinate)) {
                $coordinate = $this->coordinateTidy($request->coordinate);
                // return $coordinate;

                $whereArr[] = ["geom.STIntersects(geometry::STGeomFromText('POLYGON(({$coordinate}))', 3826))", '=', 1];
            }
            $table = indicator_model::indicator_unionAll_v2($selectArr, $whereArr);
            // return $table->get();

            return $table;
            // return response()->apiResponse(101, $output);
        } catch (\Throwable $e) {
            return response()->apiFail(100, $e);
        }
    }

    /**
     * 地圖搜尋-缺失案件資料
     */
    public function mapSelectMissing(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'coordinate'   => 'required|array|min:3',
            'coordinate.*' => 'required|size:2',
        ], [
            // 自訂回傳錯誤訊息
            // !!!!!!!!!!!!!需順時針給!!!!!!!!!!!!!
            'coordinate'            => '【座標】必填且需為陣列及需大於三組',
            'coordinate.*.required' => '【座標】必填',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator);
        }

        try {
            $selectArr = [DB::raw('geom.STAsText() as STAsText'), 'qgs_fid'];
            if (!empty($request->coordinate)) {
                $coordinate = $this->coordinateTidy($request->coordinate);
                // return $coordinate;

                $whereArr[] = ["geom.STIntersects(geometry::STGeomFromText('POLYGON(({$coordinate}))', 3826))", '=', 1];
            }
            $table = missing_model::missing_unionAll_v2($selectArr, $whereArr);
            // return $table->get();

            return $table;
            // return response()->apiResponse(101, $output);
        } catch (\Throwable $e) {
            return response()->apiFail(100, $e);
        }
    }

    /**
     * 地圖搜尋-缺失案件資料
     */
    public function mapSelectMissingRoad(Request $request)
    {
        // 參數驗證
        $validator = Validator::make($request->all(), [
            // 驗證規則
            'coordinate'   => 'required|array|min:3',
            'coordinate.*' => 'required|size:2',
        ], [
            // 自訂回傳錯誤訊息
            // !!!!!!!!!!!!!需順時針給!!!!!!!!!!!!!
            'coordinate'            => '【座標】必填且需為陣列及需大於三組',
            'coordinate.*.required' => '【座標】必填',
        ]);
        // 錯誤回傳
        if ($validator->fails()) {
            return response()->failureMessages($validator);
        }

        try {
            $selectArr = [DB::raw('geom.STAsText() as STAsText'), 'qgs_fid'];
            if (!empty($request->coordinate)) {
                $coordinate = $this->coordinateTidy($request->coordinate);
                // return $coordinate;

                $whereArr[] = ["geom.STIntersects(geometry::STGeomFromText('POLYGON(({$coordinate}))', 3826))", '=', 1];
            }
            $table = missingRoad_model::missingRoad_unionAll_v2($selectArr, $whereArr);
            // return $table->get();

            return $table;
            // return response()->apiResponse(101, $output);
        } catch (\Throwable $e) {
            return response()->apiFail(100, $e);
        }
    }
}

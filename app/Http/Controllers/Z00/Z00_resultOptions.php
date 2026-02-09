<?php

namespace App\Http\Controllers\Z00;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Z00\Z00_matchupResultList;
use App\Models\Z00\Z00_positionAndLocation;
use App\Models\Z00\Z00_ballInPlayType;
use App\Models\Z00\Z00_matchupOptions;

class Z00_resultOptions extends Controller
{
    /**
     * Display the specified resource.
     * 回傳該筆資料查詢資訊
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function Z00_matchupResultList()
    {
        try {
            $table = Z00_matchupResultList::with('options')
                ->select('id', 'code', 'name', 'jaName')
                ->orderBy('orderNo')
                ->get();
            // return $table;

            foreach ($table as $value) {
                $data[] = [
                    'Z00_matchupResultList_id' => $value['id'],
                    'code' => $value['code'],
                    'name' => $value['name'],
                    'jaName' => $value['jaName'],
                    'ballTypeOptions' => explode(',', $value['options']['ballTypeOptions']),
                    'locationOptions' => explode(',', $value['options']['locationOptions']),
                ];
            }
            return response()->apiResponse($data);
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    public function Z00_positionAndLocation(int $Z00_matchupResultList_id)
    {
        try {
            $data = [];
            $Z00_matchupOptions = [];
            if ($Z00_matchupResultList_id > 0) {
                $Z00_matchupOptions = Z00_matchupOptions::where('Z00_matchupResultList_id', $Z00_matchupResultList_id)
                    ->select('locationOptions')->first();
            }

            $table = Z00_positionAndLocation::select('id', 'code', 'name', 'jaName')
                ->when($Z00_matchupResultList_id > 0, function ($query) use ($Z00_matchupOptions) {
                    $locationOptions = explode(',', $Z00_matchupOptions->locationOptions);
                    return $query->whereIn('id', $locationOptions);
                })
                ->orderBy('orderNo')
                ->get();
            // return $table;

            foreach ($table as $value) {
                $data[] = [
                    'Z00_positionAndLocation_id' => $value['id'],
                    'code' => $value['code'],
                    'name' => $value['name'],
                    'jaName' => $value['jaName'],
                ];
            }

            return response()->apiResponse($data);
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }

    public function Z00_ballInPlayType(int $Z00_matchupResultList_id)
    {
        try {
            $data = [];
            $Z00_matchupOptions = [];
            if ($Z00_matchupResultList_id > 0) {
                $Z00_matchupOptions = Z00_matchupOptions::where('Z00_matchupResultList_id', $Z00_matchupResultList_id)
                    ->select('ballTypeOptions')->first();
            }

            $table = Z00_ballInPlayType::select('id', 'code', 'name', 'jaName')
                ->when($Z00_matchupResultList_id > 0, function ($query) use ($Z00_matchupOptions) {
                    $ballTypeOptions = explode(',', $Z00_matchupOptions->ballTypeOptions);
                    return $query->whereIn('id', $ballTypeOptions);
                })
                ->orderBy('orderNo')
                ->get();

            foreach ($table as $value) {
                $data[] = [
                    'Z00_ballInPlayType_id' => $value['id'],
                    'code' => $value['code'],
                    'name' => $value['name'],
                    'jaName' => $value['jaName'],
                ];
            }

            return response()->apiResponse($data);
        } catch (\Throwable $e) {
            return response()->apiFail($e);
        }
    }
}

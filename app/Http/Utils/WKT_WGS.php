<?php

namespace App\Http\Utils;

/**
 * WKT_WGS
 */
class WKT_WGS
{
    // 麥卡托投影轉經緯度 WKID=102100->4326
    public static function mercatorToLonLat($x, $y)
    {
        $toX = $x / 20037508.34 * 180;
        $toY = $y / 20037508.34 * 180;
        $toY = 180 / M_PI * (2 * atan(exp($toY * M_PI / 180)) - M_PI / 2);

        return [
            0 => $toX,
            1 => $toY,
        ];
    }

    // TWD97 X/Y座標 轉為 WGS 84 經緯度 圖資版
    public static function WKT_TWD97_To_WGS84($x, $y)
    {
        $a = 6378137.0;
        $b = 6356752.3142451;
        $lon0 = 121 * pi() / 180;
        $k0 = 0.9999;
        $dx = 250000;
        $dy = 0;
        $e = 1 - pow($b, 2) / pow($a, 2);
        $e2 = ($e) / (pow($b, 2) / pow($a, 2));
        $x -= $dx;
        $y -= $dy;

        // Calculate the Meridional Arc
        $M = $y / $k0;

        // Calculate Footprint Latitude
        $mu = $M / ($a * (1.0 - $e / 4.0 - 3 * pow($e, 2) / 64.0 - 5 * pow($e, 3) / 256.0));
        $e1 = (1.0 - sqrt(1.0 - $e)) / (1.0 + sqrt(1.0 - $e));

        $J1 = (3 * $e1 / 2 - 27 * pow($e1, 3) / 32.0);
        $J2 = (21 * pow($e1, 2) / 16 - 55 * pow($e1, 4) / 32.0);
        $J3 = (151 * pow($e1, 3) / 96.0);
        $J4 = (1097 * pow($e1, 4) / 512.0);

        $fp = $mu + $J1 * sin(2 * $mu) + $J2 * sin(4 * $mu) + $J3 * sin(6 * $mu) + $J4 * sin(8 * $mu);

        // Calculate Latitude and Longitude
        $C1 = $e2 * pow(cos($fp), 2);
        $T1 = pow(tan($fp), 2);
        $R1 = $a * (1 - $e) / pow((1 - $e * pow(sin($fp), 2)), (3.0 / 2.0));
        $N1 = $a / sqrt(1 - $e * pow(sin($fp), 2));

        $D = $x / ($N1 * $k0);

        // 計算緯度
        $Q1 = $N1 * tan($fp) / $R1;
        $Q2 = (pow($D, 2) / 2.0);
        $Q3 = (5 + 3 * $T1 + 10 * $C1 - 4 * pow($C1, 2) - 9 * $e2) * pow($D, 4) / 24.0;
        $Q4 = (61 + 90 * $T1 + 298 * $C1 + 45 * pow($T1, 2) - 3 * pow($C1, 2) - 252 * $e2) * pow($D, 6) / 720.0;
        $lat = $fp - $Q1 * ($Q2 - $Q3 + $Q4);

        // 計算經度
        $Q5 = $D;
        $Q6 = (1 + 2 * $T1 + $C1) * pow($D, 3) / 6;
        $Q7 = (5 - 2 * $C1 + 28 * $T1 - 3 * pow($C1, 2) + 8 * $e2 + 24 * pow($T1, 2)) * pow($D, 5) / 120.0;
        $lng = $lon0 + ($Q5 - $Q6 + $Q7) / cos($fp);

        $lat = ($lat * 180) / pi(); // 緯度
        $lng = ($lng * 180) / pi(); // 經度

        return [
            'lng' => round($lng, 8),
            'lat' => round($lat, 8),
        ];
    }

    // WGS 84 經緯度轉為 TWD97 X/Y座標 圖資版
    public static function WKT_WGS84_To_TWD97($lng, $lat)
    {
        $a = 6378137.0;
        $b = 6356752.3142451;
        $lon0 = 121 * pi() / 180;
        $k0 = 0.9999;
        $dx = 250000;
        $dy = 0;
        $e = 1 - pow($b, 2) / pow($a, 2);
        $e2 = ($e) / (pow($b, 2) / pow($a, 2));

        $lng = ($lng - floor(($lng + 180) / 360) * 360) * pi() / 180;
        $lat = $lat * pi() / 180;

        $V = $a / sqrt(1 - $e * pow(sin($lat), 2));
        $T = pow(tan($lat), 2);
        $C = $e2 * pow(cos($lat), 2);
        $A = cos($lat) * ($lng - $lon0);
        $M = $a * ((1.0 - $e / 4.0 - 3.0 * pow($e, 2) / 64.0 - 5.0 * pow($e, 3) / 256.0) * $lat -
            (3.0 * $e / 8.0 + 3.0 * pow($e, 2) / 32.0 + 45.0 * pow($e, 3) / 1024.0) *
            sin(2.0 * $lat) + (15.0 * pow($e, 2) / 256.0 + 45.0 * pow($e, 3) / 1024.0) *
            sin(4.0 * $lat) - (35.0 * pow($e, 3) / 3072.0) * sin(6.0 * $lat));

        // x
        $x = $dx + $k0 * $V * ($A + (1 - $T + $C) * pow($A, 3) / 6 + (5 - 18 * $T + pow($T, 2) + 72 * $C - 58 * $e2) * pow($A, 5) / 120);

        // y
        $y = $dy + $k0 * ($M + $V * tan($lat) * (pow($A, 2) / 2 + (5 - $T + 9 * $C + 4 * pow($C, 2)) * pow($A, 4) / 24 + (61 - 58 * $T + pow($T, 2) + 600 * $C - 330 * $e2) * pow($A, 6) / 720));

        return [
            'x' => round($x, 3),
            'y' => round($y, 3),
        ];
    }

    public static function WKT_Rep_TWD97_To_WGS84($representation)
    {
        $text = trim($representation);
        if (!preg_match('/^((?:(?:Multi)?(?:Point|LineString|Polygon)|GeometryCollection)\s*\()(.*)(\))$/ui', $text, $matches)) {
            return false;
        }
        $re_number = '[+-]?(?:(?:\\d+\\.?\\d*)|(?:\\.\\d+))';

        return $matches[1] . preg_replace_callback("/([,(]\\s*)({$re_number})(\\s+)({$re_number})(?=\\s*[,)])/ui", function ($matches) {
            // echo $matches[2], ' ', $matches[4], "\n";
            $coords = self::WKT_TWD97_To_WGS84($matches[2], $matches[4]);

            return $matches[1] . $coords['lng'] . $matches[3] . $coords['lat'];
        }, $matches[2]) . $matches[3];
        /*
        if (!preg_match_all('/(\d+)\s+(\d+)/ui', $matches[1], $all_matches, PREG_SET_ORDER)) {   // default: PREG_PATTERN_ORDER
            return false;
        }
        $coords = [];
        foreach ($all_matches as $matches) {
            $coords[] = self::WKT_TWD97_To_WGS84($matches[1], $matches[2]);
        }
        return $$coords;
        */
    }

    public static function WKT_Rep_WGS84_To_TWD97($representation)
    {
        $text = trim($representation);
        if (!preg_match('/^((?:(?:Multi)?(?:Point|LineString|Polygon)|GeometryCollection)\s*\()(.*)(\))$/ui', $text, $matches)) {
            return false;
        }
        $re_number = '[+-]?(?:(?:\\d+\\.?\\d*)|(?:\\.\\d+))';

        return $matches[1] . preg_replace_callback("/([,(]\\s*)({$re_number})(\\s+)({$re_number})(?=\\s*[,)])/ui", function ($matches) {
            // echo $matches[2], ' ', $matches[4], "\n";
            $coords = self::WKT_WGS84_To_TWD97($matches[2], $matches[4]);

            return $matches[1] . $coords['x'] . $matches[3] . $coords['y'];
        }, $matches[2]) . $matches[3];
        /*
        if (!preg_match_all('/(\d+)\s+(\d+)/ui', $matches[1], $all_matches, PREG_SET_ORDER)) {   // default: PREG_PATTERN_ORDER
            return false;
        }
        $coords = [];
        foreach ($all_matches as $matches) {
            $coords[] = WKT_WGS84_To_TWD97($matches[1], $matches[2]);
        }
        return $$coords;
        */
    }

    //20191226 TW67轉TW97
    public static function WKT_TWD67_To_TWD97($lon, $lat)
    {
        $a    = 6378137.0;
        $b    = 6356752.314245;
        $lon0 = 121 * M_PI / 180;
        $k0   = 0.9999;
        $dx   = 250000;

        $lon = ($lon / 180) * M_PI;
        $lat = ($lat / 180) * M_PI;
        $e   = pow((1 - pow($b, 2) / pow($a, 2)), 0.5);
        $e2  = pow($e, 2) / (1 - pow($e, 2));
        $n   = ($a - $b) / ($a + $b);
        $nu  = $a / pow((1 - (pow($e, 2)) * (pow(sin($lat), 2))), 0.5);
        $p   = $lon - $lon0;
        $A   = $a * (1 - $n + (5 / 4) * (pow($n, 2) - pow($n, 3)) + (81 / 64) * (pow($n, 4) - pow($n, 5)));
        $B   = (3 * $a * $n / 2.0) * (1 - $n + (7 / 8.0) * (pow($n, 2) - pow($n, 3)) + (55 / 64.0) * (pow($n, 4) - pow($n, 5)));
        $C   = (15 * $a * (pow($n, 2)) / 16.0) * (1 - $n + (3 / 4.0) * (pow($n, 2) - pow($n, 3)));
        $D   = (35 * $a * (pow($n, 3)) / 48.0) * (1 - $n + (11 / 16.0) * (pow($n, 2) - pow($n, 3)));
        $E   = (315 * $a * (pow($n, 4)) / 51.0) * (1 - $n);

        $S = $A * $lat - $B * sin(2 * $lat) + $C * sin(4 * $lat) - $D * sin(6 * $lat) + $E * sin(8 * $lat);
        //計算Y值
        $K1 = $S * $k0;
        $K2 = $k0 * $nu * sin(2 * $lat) / 4.0;
        $K3 = ($k0 * $nu * sin($lat) * (pow(cos($lat), 3)) / 24.0) * (5 - pow(tan($lat), 2) + 9 * $e2 * pow((cos($lat)), 2) + 4 * (pow($e2, 2)) * (pow(cos($lat), 4)));
        $y  = $K1 + $K2 * (pow($p, 2)) + $K3 * (pow($p, 4));

        //計算X值
        $K4 = $k0 * $nu * cos($lat);
        $K5 = ($k0 * $nu * (pow(cos($lat), 3)) / 6.0) * (1 - pow(tan($lat), 2) + $e2 * (pow(cos($lat), 2)));
        $x  = $K4 * $p + $K5 * (pow($p, 3)) + $dx;

        //floor(x); 無條件捨去
        //round(y); 四捨五入
        return [
            'x' => round($x, 2),
            'y' => round($y, 2),
        ];
    }

    //2021-12-06 polygon to Array 字串轉陣列
    public function WKT_to_Array($representation)
    {
        $text = trim($representation);
        if (!preg_match('/^((?:(?:Multi)?(?:Point|LineString|Polygon)|GeometryCollection)\s*\()(.*)(\))$/ui', $text, $matches)) {
            return false;
        }

        $coord_pattern = '[+-]?(?:(?:\\d+\\.?\\d*)|(?:\\.\\d+))';

        $coords_str = $matches[2];

        if (!preg_match_all("/({$coord_pattern})(?:\\s+)({$coord_pattern})(?=\\s*(?:,|$))/ui", $coords_str, $coord_matches, PREG_SET_ORDER)) {   // default: PREG_PATTERN_ORDER
            return false;
        }

        // $coords = array_map(fn ($coord) => [$coord[1], $coord[2]], $coord_matches);
        $coords = array_map(fn($coord) => [$coord[2], $coord[1]], $coord_matches);

        return $coords;
    }
}

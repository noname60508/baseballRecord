<?php

namespace App\Http\Utils;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CommonMigration
{
    /**
     * 時間戳
     */
    public static function basicTimestamp(Blueprint &$table): void
    {
        $table->timestamps();
        $table->softDeletes();
        $table->unsignedBigInteger('created_by')->nullable()->comment('資料建立人員');
        $table->unsignedBigInteger('updated_by')->nullable()->comment('最後編輯人員');
        $table->unsignedBigInteger('deleted_by')->nullable()->comment('最後刪除人員');
    }

    /**
     * 經緯度
     */
    public static function wgs84(Blueprint &$table): void
    {
        $table->decimal('longitude', 11, 8)->nullable()->comment('經度');
        $table->decimal('latitude', 10, 8)->nullable()->comment('緯度');
        // $table->geometry('geom')->nullable()->comment('經緯度geometry'); // 不知道為什麼會變geography 改在新增trigger時建置
    }

    /**
     * twd97 平面座標
     */
    public static function twd97(Blueprint &$table): void
    {
        $table->decimal('twd_x', 9, 3)->nullable()->comment('x');
        $table->decimal('twd_y', 10, 3)->nullable()->comment('y');
        // $table->geometry('twd_geom')->nullable()->comment('twd97 geometry');
    }

    /**
     * 經緯度更新地理資訊trigger
     */
    public static function wgs84Trigger($tableName): void
    {
        DB::unprepared("ALTER TABLE {$tableName} ADD geom geometry");
        // 更新geom trigger
        DB::unprepared("CREATE TRIGGER {$tableName}_update_geom
                ON {$tableName}
                AFTER INSERT,UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;
                DECLARE @Operation char(1) = '';

                IF EXISTS(SELECT 1 FROM inserted) AND NOT EXISTS(SELECT 1 FROM deleted)
                    SET @Operation = 'I'        --Insert

                IF EXISTS(SELECT 1 FROM inserted) AND EXISTS(SELECT 1 FROM deleted)
                    SET @Operation = 'U'        --Update

                IF (@Operation = 'I')
                    BEGIN
                        IF ((UPDATE([longitude]) OR UPDATE([latitude])) AND EXISTS(SELECT 1 FROM [INSERTED] WHERE LEN(ISNULL([longitude], '')) > 0 AND LEN(ISNULL([latitude], '')) > 0))
                            BEGIN
                                --建立GEOM & GEOG
                                UPDATE [TARGET] SET [geom] = geometry::STGeomFromText('POINT(' + CONVERT(varchar,[TARGET].[longitude]) + ' ' + CONVERT(varchar,[TARGET].[latitude]) + ')', 4326)
                                FROM {$tableName} AS [TARGET] INNER JOIN [INSERTED] AS [SOURCE] ON [TARGET].[id]=[SOURCE].[id];
                            END
                    END
                IF (@Operation = 'U')
                    BEGIN
                        IF ((UPDATE([longitude]) OR UPDATE([latitude])) AND EXISTS(SELECT 1 FROM [INSERTED] WHERE LEN(ISNULL([longitude], '')) > 0 AND LEN(ISNULL([latitude], '')) > 0))
                            BEGIN
                                --建立GEOM & GEOG
                                UPDATE [TARGET] SET [geom] = geometry::STGeomFromText('POINT(' + CONVERT(varchar,[TARGET].[longitude]) + ' ' + CONVERT(varchar,[TARGET].[latitude]) + ')', 4326)
                                FROM {$tableName} AS [TARGET] INNER JOIN [INSERTED] AS [SOURCE] ON [TARGET].[id]=[SOURCE].[id]
                            END
                    END
            END
        ");
    }

    /**
     * twd97更新地理資訊trigger
     */
    public static function twd97Trigger($tableName): void
    {
        DB::unprepared("ALTER TABLE {$tableName} ADD twd_geom geometry");
        // 更新geom trigger
        DB::unprepared("CREATE TRIGGER {$tableName}_update_twd_geom
                ON {$tableName}
                AFTER INSERT,UPDATE
            AS
            BEGIN
                SET NOCOUNT ON;
                DECLARE @Operation char(1) = '';

                IF EXISTS(SELECT 1 FROM inserted) AND NOT EXISTS(SELECT 1 FROM deleted)
                    SET @Operation = 'I'        --Insert

                IF EXISTS(SELECT 1 FROM inserted) AND EXISTS(SELECT 1 FROM deleted)
                    SET @Operation = 'U'        --Update

                IF (@Operation = 'I')
                    BEGIN
                        IF ((UPDATE([twd_x]) OR UPDATE([twd_y])) AND EXISTS(SELECT 1 FROM [INSERTED] WHERE LEN(ISNULL([twd_x], '')) > 0 AND LEN(ISNULL([twd_y], '')) > 0))
                            BEGIN
                                --建立GEOM & GEOG
                                UPDATE [TARGET] SET [twd_geom] = geometry::STGeomFromText('POINT(' + CONVERT(varchar,[TARGET].[twd_x]) + ' ' + CONVERT(varchar,[TARGET].[twd_y]) + ')', 4326)
                                FROM {$tableName} AS [TARGET] INNER JOIN [INSERTED] AS [SOURCE] ON [TARGET].[id]=[SOURCE].[id];
                            END
                    END
                IF (@Operation = 'U')
                    BEGIN
                        IF ((UPDATE([twd_x]) OR UPDATE([twd_y])) AND EXISTS(SELECT 1 FROM [INSERTED] WHERE LEN(ISNULL([twd_x], '')) > 0 AND LEN(ISNULL([twd_y], '')) > 0))
                            BEGIN
                                --建立GEOM & GEOG
                                UPDATE [TARGET] SET [twd_geom] = geometry::STGeomFromText('POINT(' + CONVERT(varchar,[TARGET].[twd_x]) + ' ' + CONVERT(varchar,[TARGET].[twd_y]) + ')', 4326)
                                FROM {$tableName} AS [TARGET] INNER JOIN [INSERTED] AS [SOURCE] ON [TARGET].[id]=[SOURCE].[id]
                            END
                    END
            END
        ");
    }


    /**
     * 檔案上傳共用模組欄位
     */
    public static function dataUpload(Blueprint &$table, string $foreignKeyName, string $categoryComment = ''): void
    {
        $table->integer('photo_or_File')->nullable()->comment('1:照片、2:檔案');
        $table->integer('category')->comment('類別:[' . $categoryComment . ']');
        $table->integer($foreignKeyName)->nullable()->comment('關聯:' . $foreignKeyName . '_id');
        $table->string('fileDir', 200)->nullable()->comment('照片路徑');
        $table->string('fileBasename', 200)->nullable()->comment('編譯名稱');
        $table->string('fileOriginalName', 200)->nullable()->comment('原始名稱');
        $table->string('note', 1000)->nullable()->comment('備註');
    }
}

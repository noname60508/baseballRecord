# Baseball Record — 系統架構說明 / システムアーキテクチャ説明

## 目錄 / 目次

- [專案概述 / プロジェクト概要](#專案概述--プロジェクト概要)
- [技術棧 / 技術スタック](#技術棧--技術スタック)
- [目錄結構 / ディレクトリ構成](#目錄結構--ディレクトリ構成)
- [模組命名規則 / モジュール命名規則](#模組命名規則--モジュール命名規則)
- [系統分層架構 / システム階層アーキテクチャ](#系統分層架構--システム階層アーキテクチャ)
- [Middleware 驗證機制 / 認証メカニズム](#middleware-驗證機制--認証メカニズム)
- [模組說明 / モジュール説明](#模組說明--モジュール説明)
  - [A00 — 認證系統 / 認証システム](#a00--認證系統--認証システム)
  - [B00 — 比賽記錄 / 試合記録](#b00--比賽記錄--試合記録)
  - [Z00 — 主資料管理 / マスタデータ管理](#z00--主資料管理--マスタデータ管理)
- [API 路由設計 / ルート設計](#api-路由設計--ルート設計)
- [資料庫設計 / データベース設計](#資料庫設計--データベース設計)
- [通用工具 / 共通ユーティリティ (Utils)](#通用工具--共通ユーティリティ-utils)

---

## 專案概述 / プロジェクト概要

**Baseball Record** 是一套棒球比賽記錄與打擊統計管理系統，提供 RESTful API 後端服務。
使用者可透過此系統記錄比賽資訊、逐打席對決結果，並查詢多種維度的打擊統計數據。

**Baseball Record** は、野球の試合記録と打撃統計管理システムであり、RESTful API バックエンドサービスを提供します。
ユーザーはこのシステムを通じて試合情報、打席ごとの対決結果を記録し、多角的な打撃統計データを照会することができます。

---

## 技術棧 / 技術スタック

| 類別 / カテゴリ | 技術 / 套件 | バージョン |
|----------------|------------|-----------|
| 執行環境 / 実行環境 | PHP | ^8.2 |
| 框架 / フレームワーク | Laravel | ^12.0 |
| API 認證 / API 認証 | Laravel Sanctum | ^4.2 |
| 時間處理 / 日時処理 | nesbot/Carbon | ^3.10 |
| 稽核欄位 / 監査フィールド | wildside/userstamps | ^3.1 |
| 日誌瀏覽 / ログビューア | opcodesio/log-viewer | ^3.19 |

---

## 目錄結構 / ディレクトリ構成

```
baseballRecord/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── A00/A10/          # 認證 Controller / 認証 Controller
│   │   │   ├── B00/B10/          # 比賽 Controller / 試合 Controller
│   │   │   ├── B00/B20/          # 打擊 Controller / 打撃 Controller
│   │   │   └── Z00/              # 主資料 Controller / マスタデータ Controller
│   │   ├── Middleware/           # 中介層 / ミドルウェア
│   │   └── Utils/                # 共用工具類別 / 共通ユーティリティクラス
│   ├── Models/
│   │   ├── B00/B10/              # 比賽 Model / 試合 Model
│   │   ├── B00/B20/              # 打擊 Model / 打撃 Model
│   │   └── Z00/                  # 主資料 Model / マスタデータ Model
│   ├── Notifications/            # 通知（忘記密碼信件 / パスワードリセットメール）
│   └── Providers/                # Service Provider
├── database/
│   ├── migrations/               # 資料庫遷移腳本 / データベースマイグレーション
│   └── seeders/                  # 初始資料 / 初期データ
├── routes/
│   ├── api.php                   # 公開路由（登入/註冊）/ 公開ルート（ログイン/登録）
│   └── api/
│       ├── A00_system_api.php    # A00 認證路由（需登入）/ 認証ルート（要ログイン）
│       ├── B00_system_api.php    # B00 比賽路由（需登入）/ 試合ルート（要ログイン）
│       └── Z00_system_api.php    # Z00 主資料路由（需登入）/ マスタデータルート（要ログイン）
└── stubs/                        # 自訂 Artisan stub 範本 / カスタム Artisan スタブテンプレート
```

---

## 模組命名規則 / モジュール命名規則

本專案採用字首數字碼來區分模組層級：  
本プロジェクトでは、英字プレフィックスと数字コードを用いてモジュール階層を区別します：

```
[英文字母 / 英字][兩位數字 / 2桁数字]
 │                └── 子模組序號 / サブモジュール番号（10, 11, 20, 21 …）
 └── 系統區塊代碼 / システムブロックコード
       A = 帳號/認證 (Account)
       B = 業務功能 (Business)
       Z = 主資料/參照資料 (Master Data)
```

| 代碼 | 中文說明 | 日本語説明 |
|------|---------|-----------|
| A00 | 帳號系統（認證、使用者管理） | アカウントシステム（認証、ユーザー管理） |
| A10 | 認證子模組 | 認証サブモジュール |
| A11 | 認證 Controller / 使用者 CRUD | 認証 Controller / ユーザー CRUD |
| B00 | 比賽業務系統 | 試合ビジネスシステム |
| B10 | 比賽子模組 | 試合サブモジュール |
| B11 | 比賽場次管理 | 試合管理 |
| B20 | 打擊記錄子模組 | 打撃記録サブモジュール |
| B21 | 打擊對決結果與統計 | 打撃対決結果と統計 |
| Z00 | 主資料（隊伍、賽季、場地、對決結果選項） | マスタデータ（チーム、シーズン、グラウンド、対決結果オプション） |

---

## 系統分層架構 / システム階層アーキテクチャ

```
┌─────────────────────────────────────────┐
│     Client (前端 / App / フロントエンド)  │
└───────────────────┬─────────────────────┘
                    │ HTTP Request (Bearer Token)
┌───────────────────▼─────────────────────┐
│             routes/api.php              │
│    routes/api/{A00,B00,Z00}_system_api  │
└───────────────────┬─────────────────────┘
                    │
┌───────────────────▼─────────────────────┐
│         Middleware: authentication      │  ← Token 驗證 / 検証 · 自動刷新 / 更新
│  · 驗證 Bearer Token 是否存在且有效       │
│  · 過期但在 1 個月內 → 自動換發新 Token   │
│  · 未過期 → 延長有效期                    │
│  · Response Header 回傳最新 Token        │
└───────────────────┬─────────────────────┘
                    │
┌───────────────────▼─────────────────────┐
│            Controllers Layer            │
│  A11_authController (認證/使用者 / 認証) │
│  B11_gamesController (比賽場次 / 試合)   │
│  B21_battingStatistics (打擊統計 / 打撃) │
│  B21_battingResult (逐打席 / 打席別記録) │
│  Z00_teamsController (隊伍 / チーム)     │
│  Z00_seasonsController (賽季 / シーズン) │
│  Z00_fieldsController (場地 / グラウンド)│
│  Z00_resultOptions (對決結果選項)        │
└───────────────────┬─────────────────────┘
                    │
┌───────────────────▼─────────────────────┐
│              Models Layer               │
│  B11_games · B21_batterResult           │
│  B21_gameLogBatter · Z00_teams          │
│  Z00_seasons · Z00_fields               │
│  Z00_matchupResultList · Z00_ballInPlayType │
│  Z00_positionAndLocation                │
└───────────────────┬─────────────────────┘
                    │
┌───────────────────▼─────────────────────┐
│              Database (MySQL)           │
└─────────────────────────────────────────┘
```

---

## Middleware 驗證機制 / 認証メカニズム

位於 `app/Http/Middleware/authentication.php`，套用於所有需要登入的 API 路由（`/api/A00`、`/api/B00`、`/api/Z00`）。  
`app/Http/Middleware/authentication.php` に位置し、ログインが必要な全 API ルートに適用されます。

### 驗證流程 / 認証フロー

```
收到請求 / リクエスト受信
  ↓
檢查 Authorization Header 是否包含 Bearer Token
Authorization Header に Bearer Token が含まれているか確認
  ├─ 無 Token → 401 回應 / 401 レスポンス
  ↓
透過 Sanctum PersonalAccessToken 查找 Token
Sanctum PersonalAccessToken で Token を検索
  ├─ Token 不存在 → 401 / Token が存在しない → 401
  ↓
比對目前時間與過期時間 / 現在時刻と有効期限を比較
  ├─ 已過期超過 1 個月  → 401（Token 完全失效 / 完全失効）
  ├─ 已過期但在 1 個月內 → 換發新 Token / 新 Token 発行，刪除舊 Token / 旧 Token 削除
  └─ 尚未過期         → 延長 Token 有效期（Sliding Expiration）
  ↓
綁定使用者身份到當前 Request / ユーザー情報をバインド (auth()->setUser)
  ↓
繼續執行 Controller，Response Header 回傳最新 Token
Controller 実行を継続し、Response Header で最新 Token を返却
```

### Token 生命週期策略 / ライフサイクル戦略

| 狀態 / 状態 | 條件 / 条件 | 處理方式 / 処理方式 |
|------------|------------|-------------------|
| **有效 / 有効** | 現在時間 < expires_at | 延長 expires_at（Sliding Expiration） |
| **過期可刷新 / 更新可能** | expires_at ≤ 現在時間 ≤ expires_at + 1 個月 | 換發新 Token，舊 Token 刪除 / 新 Token 発行、旧 Token 削除 |
| **完全失效 / 完全失効** | 現在時間 > expires_at + 1 個月 | 拒絕存取，回傳 401 / アクセス拒否、401 返却 |

> 每次成功驗證後，最新的 Token 會透過 `Authorization: Bearer <token>` 回傳於 Response Header，客戶端應更新本地儲存的 Token。  
> 認証成功のたびに、最新の Token が Response Header で返却されます。クライアントはローカルに保存している Token を更新してください。

---

## 模組說明 / モジュール説明

### A00 — 認證系統 / 認証システム

負責使用者帳號的建立、認證與管理。透過 **Laravel Sanctum** 發放 Token。  
ユーザーアカウントの作成、認証、管理を担当します。**Laravel Sanctum** を通じて Token を発行します。

| Controller | 中文功能 / 日本語機能 |
|------------|---------------------|
| `A11_authController` | 登入/ログイン、登出/ログアウト、註冊/登録、忘記密碼/パスワードリセット、修改密碼/パスワード変更、更新頭像/アバター更新 |

**公開路由（不需 Token）/ 公開ルート（Token 不要）：**
- `POST /login`
- `POST /register`
- `POST /forgotPassword`
- `POST /resetForgotPassword`

**需登入的路由 / ログイン必須ルート（`/api/A00/...`）：**
- `GET/PUT/DELETE /A10/A11_authController` — 使用者查詢/更新/刪除 / ユーザー照会/更新/削除
- `POST /A10/A11_authController/register` — 新增使用者 / ユーザー追加
- `POST /A10/A11_authController/iconUpdate` — 更新頭像 / アバター更新
- `POST /A10/A11_authController/changePassword` — 修改密碼 / パスワード変更
- `GET /A10/A11_authController/logout` — 登出 / ログアウト

---

### B00 — 比賽記錄 / 試合記録

比賽業務核心，負責比賽場次的建立與查詢，以及逐打席對決結果與統計分析。  
試合ビジネスのコア。試合の作成・照会、打席ごとの対決結果と統計分析を担当します。

#### B10 — 比賽場次管理 / 試合管理

| Controller | Model | 中文功能 / 日本語機能 |
|------------|-------|---------------------|
| `B11_gamesController` | `B11_games` | 比賽場次的 CRUD（隊伍、賽季、場地、比數）/ 試合の CRUD（チーム、シーズン、グラウンド、スコア） |

#### B20 — 打擊記錄管理 / 打撃記録管理

| Controller | Model | 中文功能 / 日本語機能 |
|------------|-------|---------------------|
| `B21_battingStatistics` | `B21_gameLogBatter` | 球員打擊數據統計查詢 / 選手打撃データ統計照会 |
| `B21_battingResult` | `B21_batterResult` | 逐打席對決結果的新增/修改/刪除 / 打席ごとの対決結果の追加/更新/削除 |

---

### Z00 — 主資料管理 / マスタデータ管理

提供系統所需的參照資料，供其他模組使用。  
システムが必要とする参照データを提供し、他のモジュールで使用されます。

| Controller | 資料表 / テーブル | 中文說明 / 日本語説明 |
|------------|-----------------|---------------------|
| `Z00_teamsController` | `z00_teams` | 隊伍資料 CRUD / チームデータ CRUD |
| `Z00_seasonsController` | `z00_seasons` | 賽季資料 CRUD / シーズンデータ CRUD |
| `Z00_fieldsController` | `z00_fields` | 比賽場地 CRUD / グラウンドデータ CRUD |
| `Z00_resultOptions` | `z00_matchup_result_list`, `z00_ball_in_play_type`, `z00_position_and_location`, `z00_matchup_options` | 對決結果、擊球型態、守備位置等選項清單（唯讀）/ 対決結果・打球種別・守備位置等オプション一覧（読み取り専用） |

---

## API 路由設計 / ルート設計

### 路由分組策略 / ルートグループ戦略

```
/api/         → 公開路由 / 公開ルート（Sanctum 守衛外 / ガード外）
/api/A00/     → 帳號系統 / アカウントシステム（需登入 / 要ログイン）
/api/B00/     → 比賽業務 / 試合ビジネス（需登入 / 要ログイン）
/api/Z00/     → 主資料 / マスタデータ（需登入 / 要ログイン）
```

### 主要 API 端點一覽 / 主要 API エンドポイント一覧

#### 公開路由（不需 Token）/ 公開ルート（Token 不要）

| 方法 | 路徑 / パス | Controller 方法 |
|------|-----------|----------------|
| POST | `/api/login` | `A11_authController@login` |
| POST | `/api/register` | `A11_authController@register` |
| POST | `/api/forgotPassword` | `A11_authController@forgotPassword` |
| POST | `/api/resetForgotPassword` | `A11_authController@resetForgotPassword` |

#### A00 — 帳號系統 / アカウントシステム（需登入 / 要ログイン）

| 方法 | 路徑 / パス | 說明 / 説明 |
|------|-----------|------------|
| GET | `/api/A00/A10/A11_authController` | `@index` — 使用者列表 / ユーザー一覧 |
| POST | `/api/A00/A10/A11_authController/register` | `@register` — 新增使用者 / ユーザー追加 |
| GET | `/api/A00/A10/A11_authController/logout` | `@logout` — 登出 / ログアウト |
| POST | `/api/A00/A10/A11_authController/iconUpdate` | `@iconUpdate` — 更新頭像 / アバター更新 |
| POST | `/api/A00/A10/A11_authController/changePassword` | `@changePassword` — 修改密碼 / パスワード変更 |
| GET | `/api/A00/A10/A11_authController/{id}` | `@show` — 查詢單一使用者 / ユーザー照会 |
| PUT/PATCH | `/api/A00/A10/A11_authController/{id}` | `@update` — 更新使用者 / ユーザー更新 |
| DELETE | `/api/A00/A10/A11_authController/{id}` | `@destroy` — 刪除使用者 / ユーザー削除 |

#### B00 — 比賽業務 / 試合ビジネス（需登入 / 要ログイン）

| 方法 | 路徑 / パス | 說明 / 説明 |
|------|-----------|------------|
| GET | `/api/B00/B10/B11_gamesController` | `@index` — 比賽場次列表 / 試合一覧 |
| POST | `/api/B00/B10/B11_gamesController` | `@store` — 新增比賽 / 試合追加 |
| GET | `/api/B00/B10/B11_gamesController/{id}` | `@show` — 查詢單場比賽 / 試合照会 |
| PUT/PATCH | `/api/B00/B10/B11_gamesController/{id}` | `@update` — 更新比賽 / 試合更新 |
| DELETE | `/api/B00/B10/B11_gamesController/{id}` | `@destroy` — 刪除比賽 / 試合削除 |
| GET | `/api/B00/B20/B21_battingStatistics` | `@index` — 打擊統計列表 / 打撃統計一覧 |
| POST | `/api/B00/B20/B21_battingStatistics` | `@store` — 新增打擊記錄 / 打撃記録追加 |
| GET | `/api/B00/B20/B21_battingStatistics/dataStatistics` | `@dataStatistics` — 統計查詢 / 統計照会 |
| GET | `/api/B00/B20/B21_battingStatistics/{id}` | `@show` — 查詢單筆統計 / 統計単件照会 |
| PUT/PATCH | `/api/B00/B20/B21_battingStatistics/{id}` | `@update` — 更新統計 / 統計更新 |
| POST | `/api/B00/B20/B21_battingResult/updateOrCreate` | `@updateOrCreate` — 逐打席新增/修改 / 打席結果追加/更新 |
| DELETE | `/api/B00/B20/B21_battingResult/destroy` | `@destroy` — 逐打席刪除 / 打席結果削除 |

#### Z00 — 主資料 / マスタデータ（需登入 / 要ログイン）

| 方法 | 路徑 / パス | 說明 / 説明 |
|------|-----------|------------|
| GET | `/api/Z00/Z00_teamsController` | `@index` — 隊伍列表 / チーム一覧 |
| POST | `/api/Z00/Z00_teamsController` | `@store` — 新增隊伍 / チーム追加 |
| PUT/PATCH | `/api/Z00/Z00_teamsController/{id}` | `@update` — 更新隊伍 / チーム更新 |
| DELETE | `/api/Z00/Z00_teamsController/{id}` | `@destroy` — 刪除隊伍 / チーム削除 |
| GET | `/api/Z00/Z00_seasonsController` | `@index` — 賽季列表 / シーズン一覧 |
| POST | `/api/Z00/Z00_seasonsController` | `@store` — 新增賽季 / シーズン追加 |
| PUT/PATCH | `/api/Z00/Z00_seasonsController/{id}` | `@update` — 更新賽季 / シーズン更新 |
| DELETE | `/api/Z00/Z00_seasonsController/{id}` | `@destroy` — 刪除賽季 / シーズン削除 |
| GET | `/api/Z00/Z00_fieldsController` | `@index` — 場地列表 / グラウンド一覧 |
| POST | `/api/Z00/Z00_fieldsController` | `@store` — 新增場地 / グラウンド追加 |
| PUT/PATCH | `/api/Z00/Z00_fieldsController/{id}` | `@update` — 更新場地 / グラウンド更新 |
| DELETE | `/api/Z00/Z00_fieldsController/{id}` | `@destroy` — 刪除場地 / グラウンド削除 |
| GET | `/api/Z00/Z00_resultOptions/Z00_matchupResultList` | 對決結果清單 / 対決結果一覧 |
| GET | `/api/Z00/Z00_resultOptions/Z00_positionAndLocation/{id}` | 守備位置與擊球落點 / 守備位置・打球落下位置 |
| GET | `/api/Z00/Z00_resultOptions/Z00_ballInPlayType/{id}` | 擊球型態 / 打球種別 |

---

## 資料庫設計 / データベース設計

### 資料表一覽 / テーブル一覧

| 資料表 / テーブル | 模組 | 中文說明 / 日本語説明 |
|-----------------|------|---------------------|
| `users` | A00 | 使用者帳號資料 / ユーザーアカウント情報 |
| `personal_access_tokens` | A00 | Sanctum Token 儲存 / Sanctum Token ストレージ |
| `z00_teams` | Z00 | 隊伍主資料 / チームマスタ |
| `z00_seasons` | Z00 | 賽季主資料 / シーズンマスタ |
| `z00_fields` | Z00 | 場地主資料 / グラウンドマスタ |
| `z00_matchup_result_list` | Z00 | 對決結果類型清單 / 対決結果タイプ一覧 |
| `z00_matchup_options` | Z00 | 對決選項設定 / 対決オプション設定 |
| `z00_ball_in_play_type` | Z00 | 擊球型態 / 打球種別 |
| `z00_position_and_location` | Z00 | 守備位置與擊球落點 / 守備位置・打球落下位置 |
| `b11_games` | B10 | 比賽場次記錄 / 試合記録 |
| `b21_batter_result` | B20 | 逐打席對決結果 / 打席別対決結果 |
| `b21_game_log_batter` | B20 | 逐場打擊統計 / 試合別打撃統計 |

### Migration 演進流程 / 実行の流れ

```
建立基礎資料表 / 基本テーブル作成（users, cache, jobs）
  → Sanctum Token 資料表 / テーブル
  → 擴充 users 欄位 / users カラム拡張（複數次更新 / 複数回更新）
  → Z00 主資料 / マスタ（teams → seasons → fields）
  → B00 業務資料 / ビジネスデータ（b11_games → matchup 結果 → 打擊記錄 / 打撃記録）
  → 新增索引（效能優化）/ インデックス追加（パフォーマンス最適化）
```

---

## 通用工具 / 共通ユーティリティ (Utils)

位於 `app/Http/Utils/`，提供各 Controller 共用的輔助功能。  
`app/Http/Utils/` に位置し、各 Controller が共通で使用するヘルパー機能を提供します。

| 檔案 / ファイル | 中文說明 / 日本語説明 |
|--------------|---------------------|
| `tools.php` | 通用工具函式（資料轉換、格式化等）/ 汎用ユーティリティ関数（データ変換、フォーマット等） |
| `commonMigration.php` | Migration 共用欄位定義（稽核欄位、軟刪除等）/ Migration 共通カラム定義（監査フィールド、ソフトデリート等） |

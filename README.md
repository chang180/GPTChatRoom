# GPT Chat Room

Laravel 13 + Inertia.js + Vue 3 的聊天學習專案，目標是整理出一個具備多聊天室、AI 問答、訊息歷史與後續即時同步能力的完整實作。

## 目前狀態

- 已完成登入後聊天流程
- 已完成 4 個固定主題聊天室：`work`、`study`、`creative`、`daily`
- 已完成訊息持久化、歷史載入、聊天室清除
- 已完成 OpenAI 串流回應
- 已完成房間內最近訊息的 AI 上下文帶入
- 已完成超過 20 則時的增量對話小結切點（後端上下文壓縮，含 DB 鎖防競爭，不顯示於 UI）
- 已完成 Ably WebSocket Phase 1
- 已完成管理員後台（`/admin`）與公開主題清除權限控管
- 目前 AI 串流使用 `SSE`，房間級即時同步使用 Ably

## 技術棧

- Backend: Laravel 13, Jetstream, Sanctum, `openai-php/laravel`, Ably broadcaster
- Frontend: Vue 3, `@inertiajs/vue3`, Inertia server v3, Vite, Tailwind CSS
- Database: SQLite
- Cache: Laravel Cache
- Test: Pest 4

**給 AI / coding agents：** 見 [`AGENTS.md`](AGENTS.md) 與 [`.cursor/rules/laravel-boost.mdc`](.cursor/rules/laravel-boost.mdc)。

## 核心功能

- 多聊天室切換
- 兩種送出模式：`direct`、`ai_query`
- GPT 串流回覆
- Markdown 訊息顯示
- 認證、Email 驗證、2FA
- 訊息分頁與快取

## 生產環境佈署

以 `git pull` 更新後，**務必**執行 `php artisan migrate --force`，並確認 `OPENAI_API_KEY` 有效。**多人即時同步**須在 staging／production 設定 Ably（本機預設 `BROADCAST_CONNECTION=log`，可不連）。細節與 agent 檢查清單見 [`docs/deployment.md`](docs/deployment.md)。

### 2026-08-28 更新（admin 角色、主題房修復、Laravel 13 升級）

給正式區 agent（Claude Code 等）用的**最小必做清單**。本次為大型 release：後端、前端、資料庫、migration 修補資料皆有變更。

#### 1. 拉程式碼後必跑指令（順序）

```bash
git pull origin master
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache   # 若慣例有快取設定
php artisan route:cache    # 可選
php artisan view:cache     # 可選
```

> **不可略過** `npm run build`：導覽列新增「管理」連結、`ChatRoom.vue` 有調整。  
> **不可略過** `php artisan migrate --force`：含 admin 欄位與**修補四主題聊天室資料**。

#### 2. 新增／確認 `.env` 變數

在既有 `OPENAI_API_KEY` 之外，正式區 `.env` 請補上（若尚未設定）：

```env
OPENAI_MODEL=gpt-5-nano
OPENAI_REQUEST_TIMEOUT=30
```

模型名稱改 `config/openai.php` 讀取 `OPENAI_MODEL`，不再寫死在程式碼。

#### 3. 本次 migration（依時間序）

| 檔案 | 作用 |
|------|------|
| `2026_08_28_140900_make_chat_rooms_user_id_nullable.php` | 若尚未跑過：允許全域主題房 `user_id = null` |
| `2026_08_28_142159_add_is_admin_to_users_table.php` | 新增 `users.is_admin`；**id 最小的一位使用者**設為 admin |
| `2026_08_28_142712_repair_global_theme_chat_rooms.php` | 修補四主題：`name` 改回中文、`user_id` 設為 `null` |

Migration 跑完後，四主題應為：`工作` / `學習` / `創意` / `日常`，且**任何登入使用者**都可切換主題。

#### 4. 功能行為變更（佈署後須知）

- **管理員後台**：`/admin`（僅 `is_admin=true` 可進入），可升降其他使用者 admin。
- **首位註冊者**（Fortify / Google）在新環境會自動成為 admin；既有正式區靠 migration 把 **user id 最小者** 設 admin。
- **清除公開主題聊天室**：僅 admin 可執行；一般使用者清除按鈕應不可用或遭拒。
- **至少保留一位 admin**：不可刪除或降級最後一位 admin（Jetstream 刪帳號、admin 後台皆擋）。
- **公開主題 bug 修復**：舊資料若把四主題綁在單一 `user_id` 或 `name` 變成英文 slug，migration `repair_global_theme_chat_rooms` 會修正。

#### 5. 佈署後驗證（建議人工或 agent 逐項確認）

- [ ] 以**非 admin** 帳號登入 → 側欄四主題顯示中文，可切換至 `學習` / `創意` / `日常`
- [ ] 以 **admin** 登入 → 導覽列有「管理」→ `/admin` 可開啟使用者列表
- [ ] admin 可清除公開主題聊天室；非 admin 不可
- [ ] 發送 AI 訊息仍正常（確認 `OPENAI_API_KEY` 有效）
- [ ] `php artisan test` 在 staging 可選跑；本 release 基準：**108 passed, 7 skipped**

#### 6. 套件版本（供 agent 對照，勿自行降版）

- Laravel **13**、`inertiajs/inertia-laravel` **3.x**、`@inertiajs/vue3` **3.x**
- Pest **4**、PHP **^8.4**
- 細節以 `composer.lock` / `AGENTS.md` 為準

#### 7. 疑難排解

| 現象 | 可能原因 | 處理 |
|------|----------|------|
| 主題仍顯示英文、無法切換 | migration 未跑或修補失敗 | `php artisan migrate --force`，再查 `chat_rooms` 四列 `user_id` 是否為 `NULL`、`name` 是否中文 |
| 看不到「管理」 | 前端未 build 或該帳號非 admin | `npm run build`；查 `users.is_admin` |
| OpenAI 失敗 | 缺 key 或 model 名稱 | 確認 `OPENAI_API_KEY`、`OPENAI_MODEL` |

完整 Ably、Google OAuth 等既有佈署說明仍見 [`docs/deployment.md`](docs/deployment.md)。

## 專案文件

- 開發文件入口：[`docs/README.md`](docs/README.md)
- 專案架構：[`docs/architecture.md`](docs/architecture.md)
- 即時通訊與 WebSocket 規劃：[`docs/realtime-websocket-plan.md`](docs/realtime-websocket-plan.md)
- 下一階段清單：[`docs/phase-2-checklist.md`](docs/phase-2-checklist.md)
- AI 開發備忘：[`.ai-dev/README.md`](.ai-dev/README.md)

## 快速開始

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run dev
php artisan serve
```

在 `.env` 至少設定（本機開發）：

```env
OPENAI_API_KEY=your_openai_api_key
OPENAI_ORGANIZATION=your_openai_organization
OPENAI_MODEL=gpt-5-nano
OPENAI_REQUEST_TIMEOUT=30
```

本機預設 **不必** 設定 Ably（`.env.example` 為 `BROADCAST_CONNECTION=log`，多人即時不同步屬正常）。若要在本機測雙瀏覽器同步，或於佇署環境啟用，請見 [`docs/deployment.md`](docs/deployment.md) § Ably 即時廣播。

## 開發指令

```bash
php artisan serve
npm run dev
php artisan test
./vendor/bin/pint
```

## 目前架構重點

- 路由集中在 `routes/web.php`
- 聊天流程由 `app/Http/Controllers/ChatRoomController.php` 主導
- OpenAI 呼叫封裝在 `app/Services/GPTService.php`
- AI 上下文與小結切點在 `app/Services/ConversationContextService.php`
- 訊息分頁與快取邏輯在 `app/Services/MessageCacheService.php`
- 前端聊天頁在 `resources/js/Pages/ChatRoom.vue`

## 後續重點

目前專案已完成第一階段房間級即時同步。現況是保留 SSE 作為 AI 串流方案，另外使用 Ably 廣播同步「使用者新訊息、AI 最終訊息、聊天室清除」。

細節請看 [`docs/realtime-websocket-plan.md`](docs/realtime-websocket-plan.md)。

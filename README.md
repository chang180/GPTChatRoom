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

以 `git pull` 更新後，**務必**執行 `php artisan migrate --force`，並確認 `OPENAI_API_KEY` 有效。細節與 agent 檢查清單見 [`docs/deployment.md`](docs/deployment.md)。

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

在 `.env` 設定：

```env
OPENAI_API_KEY=your_openai_api_key
OPENAI_ORGANIZATION=your_openai_organization
BROADCAST_CONNECTION=ably
ABLY_KEY=your_ably_key
ABLY_TOKEN_EXPIRY=3600
VITE_ABLY_ENABLED=true
```

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

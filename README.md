# GPT Chat Room

Laravel 12 + Inertia.js + Vue 3 的聊天學習專案，目標是整理出一個具備多聊天室、AI 問答、訊息歷史與後續即時同步能力的完整實作。

## 目前狀態

- 已完成登入後聊天流程
- 已完成 4 個固定主題聊天室：`work`、`study`、`creative`、`daily`
- 已完成訊息持久化、歷史載入、聊天室清除
- 已完成 OpenAI 串流回應
- 目前前端即時回應方案是 `SSE`，尚未導入瀏覽器之間同步的 `WebSocket`

## 技術棧

- Backend: Laravel 12, Jetstream, Sanctum, OpenAI PHP SDK
- Frontend: Vue 3, Inertia.js, Vite, Tailwind CSS
- Database: SQLite
- Cache: Laravel Cache
- Test: Pest

## 核心功能

- 多聊天室切換
- 兩種送出模式：`direct`、`ai_query`
- GPT 串流回覆
- Markdown 訊息顯示
- 認證、Email 驗證、2FA
- 訊息分頁與快取

## 專案文件

- 開發文件入口：[`docs/README.md`](docs/README.md)
- 專案架構：[`docs/architecture.md`](docs/architecture.md)
- 即時通訊與 WebSocket 規劃：[`docs/realtime-websocket-plan.md`](docs/realtime-websocket-plan.md)
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
- 訊息分頁與快取邏輯在 `app/Services/MessageCacheService.php`
- 前端聊天頁在 `resources/js/Pages/ChatRoom.vue`

## 後續重點

目前專案下一個主要階段是補上真正的房間級即時同步。規劃上會保留現在的 SSE 作為 AI 串流方案，另外新增 WebSocket 廣播來同步「使用者新訊息、AI 最終訊息、聊天室清除、在線狀態」。

細節請看 [`docs/realtime-websocket-plan.md`](docs/realtime-websocket-plan.md)。

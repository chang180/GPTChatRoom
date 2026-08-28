# 專案架構

本文件以目前程式碼為準，描述 GPT Chat Room 的實際結構，而不是理想中的最終狀態。

## 1. 技術組成

- Backend: Laravel 13, PHP 8.4
- Frontend: Vue 3, Inertia.js 2, Vite 6
- Auth: Laravel Jetstream + Sanctum + Fortify
- AI: `openai-php/laravel`
- Database: SQLite
- Cache: Laravel Cache，專案內已有訊息快取服務

## 2. 目前目錄重點

```text
app/
  Http/Controllers/
    ChatRoomController.php
    PrivateChatRoomController.php
    GoogleAuthController.php
    HomeController.php
  Models/
    ChatRoom.php
    ChatRoomMember.php
    ChatRoomInvitation.php
    Message.php
    User.php
  Policies/
    ChatRoomPolicy.php
  Services/
    GPTService.php
    MessageCacheService.php
resources/
  js/
    Layouts/AppLayout.vue
    Components/ChatSidebar.vue
    Pages/ChatRoom.vue
    Pages/Dashboard.vue
    Pages/Welcome.vue
    bootstrap.js
routes/
  web.php
database/
  migrations/
config/
  openai.php
docs/
  README.md
  architecture.md
  realtime-websocket-plan.md
```

## 3. 後端責任分配

### `app/Http/Controllers/ChatRoomController.php`

處理聊天主流程：

- `index()`: 載入聊天室頁面與首批訊息
- `loadMoreMessages()`: 載入更多歷史訊息
- `sendMessage()`: 儲存使用者訊息，必要時先確保對話小結切點，再同步呼叫 GPT
- `sendMessageStream()`: 儲存使用者訊息後，先確保對話小結切點，再以 SSE 串流 GPT 回覆
- `clearChatRoom()`: 清除指定主題聊天室的全部訊息

### `app/Services/GPTService.php`

負責對 OpenAI 發送請求：

- `sendMessage()`: 一次性回應
- `sendMessageStream()`: 串流回應

模型由 `config/openai.php` 讀取 `OPENAI_MODEL`（預設 `gpt-5.6-luna`），且 controller 會傳入當前聊天室最近一段訊息作為上下文。

### `app/Services/ConversationContextService.php`

負責 AI 對話上下文：

- `ensureSummaryCheckpoint()`: 當 overflow 訊息數達門檻時，以 `SummarizeConversationJob`（`afterResponse`）非同步小結，不阻塞 AI 回覆
- `runSummarizeCheckpoint()`: 搶 `summarizing_at` 鎖後呼叫 `GPTService::summarizeMessages()`，拼接／壓縮後 upsert
- `buildConversationContext()`: 組出「小結切點（若有）+ 最近 20 則原文」送給 GPT

小結僅供後端 API 使用，不會出現在聊天 UI。

### `app/Services/MessageCacheService.php`

負責訊息查詢快取：

- 以頁碼、每頁筆數、聊天室 ID 組成 cache key
- 新訊息進來時，只清掉第一頁快取
- 已有訊息統計與預熱方法，但目前控制器主要使用分頁快取

## 4. 前端責任分配

### `resources/js/Pages/ChatRoom.vue`

目前是聊天核心頁面，主要職責：

- 顯示當前聊天室與歷史訊息
- 管理 `direct` / `ai` 兩種送出模式
- 以 `axios` 呼叫聊天 API
- 用 `onDownloadProgress` 手動解析 SSE 片段
- 訂閱 Ably 房間事件並同步其他使用者操作
- 處理無限滾動載入更多歷史訊息
- 清除聊天室與切換主題／私人聊天室（`ChatSidebar` + `roomMode`）
- 私人房以 `room` 參數呼叫 API；主題房以 `theme` slug

注意：

- AI 串流是 HTTP response stream，不是 WebSocket
- 目前使用 `@ably/laravel-echo`
- **主題房**（`global_theme`）：public channel `chat-room.{id}` → `Echo.channel()`
- **私人房**（`private_group`）：`PrivateChannel` 同名 → `Echo.private()`，授權見 `routes/channels.php`

### `resources/js/bootstrap.js`

目前會：

- 初始化 `axios`
- 初始化 Ably Echo client
- 以安全條件注入 `X-Socket-ID`
- 使用 Laravel `broadcasting/auth` 作為授權端點

## 5. 路由

`routes/web.php` 聊天與 OAuth 重點（多數在 `auth` middleware 下）：

- Google（`config('services.google.enabled')` 為 false 時 redirect 404）：`auth/google/*`、`user/google/link|unlink`
- `GET /chat`、`GET /chat/{theme}`（主題房）
- `GET|POST /chat/private`、`GET /chat/private/{chatRoom}`、邀請與 accept、移除成員
- `GET /chat/load-more`、`POST /chat/send-message`、`POST /chat/send-message-stream`、`DELETE /chat/clear`（主題用 `theme`，私人用 `room`）
- `GET|POST /broadcasting/auth`

## 6. 資料模型

### `chat_rooms`

用途：

- 存放聊天室定義
- `type`：`global_theme`（四主題）或 `private_group`（邀請制小群）
- `created_by`：私人房建立者（owner）

關鍵欄位：

- `id`, `name`, `slug`, `description`, `user_id`, `is_active`, `type`, `created_by`

主題聊天室 slug：`work`、`study`、`creative`、`daily`

相關表：`chat_room_members`（成員）、`chat_room_invitations`（邀請 token / 過期 / revoke）

### `conversation_summaries`

用途：

- 每個聊天室最多一筆對話小結切點，供 AI 上下文壓縮

關鍵欄位：

- `chat_room_id`（unique）
- `content`
- `summarized_up_to_message_id`
- `summarizing_at` / `summarizing_until`（小結進行中鎖，含逾時自動釋放）

### `messages`

用途：

- 儲存所有聊天訊息

關鍵欄位：

- `id`
- `user_id`
- `chat_room_id`
- `text`
- `sender_type`
- `created_at`
- `updated_at`

`sender_type` 目前可見值：

- `user`
- `gpt`
- `error`

## 7. 現在的訊息流程

### 直接訊息

1. 前端呼叫 `POST /chat/send-message`
2. 後端寫入一筆 `sender_type=user`
3. 後端廣播聊天室事件到 Ably
4. 直接回傳成功，不呼叫 GPT

### AI 問答

1. 前端先在畫面插入使用者訊息與空白 GPT 訊息
2. 前端呼叫 `POST /chat/send-message-stream`
3. 後端先寫入使用者訊息
4. 後端廣播使用者訊息到 Ably
5. 後端整理當前聊天室最近訊息作為上下文
6. 後端呼叫 OpenAI streamed chat
7. 後端以 SSE `data: ...` 分段輸出內容
8. 前端累加 GPT 文字
9. 串流完成後，後端再寫入一筆 `sender_type=gpt`
10. 後端再廣播 GPT 完整訊息到 Ably

### 清除聊天室

1. 前端呼叫 `DELETE /chat/clear`
2. 後端刪除當前房間訊息
3. 後端廣播清除事件到 Ably
4. 同房其他頁面同步清空

## 8. 現有設計限制

- GPT 串流 token 仍只回到發送請求的那個瀏覽器
- `clearChatRoom()` 會清空整個主題聊天室資料，因此目前是全域清除，不是清除個人視角
- 目前沒有 presence channel、typing、已讀設計
- 私人房已使用 `PrivateChannel` + Policy；主題房仍為 public channel
- 私人房成員移除等僅後端 API，前端 UI 未完整

## 9. 下一步建議

- 保留 SSE 作為 AI token 串流
- 保留目前 Ably 處理房間級即時同步
- 優先同步的事件：
  - 使用者新訊息
  - GPT 完整回覆落庫完成
  - 聊天室清除
  - 之後再補使用者加入 / 離開房間

詳細方案見 [`realtime-websocket-plan.md`](realtime-websocket-plan.md)。

# GPT Chat Room - AI 開發指南

這份文件給後續開發代理或開發者快速掌握目前專案狀態。內容以現有程式碼為準，若與其他舊文件衝突，以本文件和 `docs/` 為主。

## 文件入口

- **Agent 總索引（套件版本 / 規範）：** [`../AGENTS.md`](../AGENTS.md) →  canonical [`.cursor/rules/laravel-boost.mdc`](../.cursor/rules/laravel-boost.mdc)
- **分 phase 實作（private-room）：** [`.ai-dev/private-room/handoff.md`](private-room/handoff.md)
- 專案開發文件：[`../docs/README.md`](../docs/README.md)
- **生產佈署提醒**：[`../docs/deployment.md`](../docs/deployment.md)（pull 後必跑 migration）
- 架構說明：[`../docs/architecture.md`](../docs/architecture.md)
- 即時通訊規劃：[`../docs/realtime-websocket-plan.md`](../docs/realtime-websocket-plan.md)

## 專案定位

這是一個 Laravel 13 + Inertia.js + Vue 3 的學習專案，主題是多聊天室 AI chat app。

目前已到一個可用階段，且已完成第一版多人即時同步。現況是：

- 有多聊天室
- 有 AI 回覆串流
- 有 Ably 房間級事件同步

## 真實功能狀態

### 已完成

- Jetstream 認證與驗證流程
- 4 個固定主題聊天室：`work`、`study`、`creative`、`daily`
- 訊息持久化
- 歷史訊息載入與分頁
- 直接訊息模式
- AI 問答模式
- GPT SSE 串流回覆
- 同房最近訊息會帶入 AI 上下文
- 超過 20 則時增量對話小結切點（`ConversationContextService` + `conversation_summaries`）
- 清除整個聊天室訊息
- Ably WebSocket Phase 1
- 多瀏覽器房間同步

### 尚未完成

- presence / typing / online users
- 更細的房間權限模型
- private / presence channel 升級策略

## 重要實作事實

### 1. 即時方案目前是 SSE + WebSocket 混合

`ChatRoomController::sendMessageStream()` 透過 `response()->stream()` 輸出 `text/event-stream`。

前端在 `resources/js/Pages/ChatRoom.vue` 使用 `axios` 的 `onDownloadProgress` 手動解析 SSE 內容。

房間級共享事件則透過 Ably + Laravel Echo 同步。

### 2. 多聊天室是固定主題房，不是每人自建房

`ChatRoom` model 目前實際使用的是 4 個全域主題聊天室。雖然 model 裡仍保留 `getDefaultForUser()`，但主流程優先走全域主題房。

### 3. OpenAI 模型目前寫死，上下文含小結切點

`app/Services/GPTService.php` 使用 `gpt-5-nano`。`ConversationContextService` 在訊息超過 20 則時會先 summarize 並寫入 `conversation_summaries`，再組「小結 + 最近 20 則」送 API。佈署時見 [`../docs/deployment.md`](../docs/deployment.md)。

### 4. 即時同步目前使用 public channel

Phase 1 目前採用 public channel `chat-room.{id}`，不是 private channel。

原因：

- 4 個主題聊天室本來就是全域共享
- 目前沒有真正的房間授權模型
- public channel 複雜度更低，適合目前階段

### 5. 快取已存在，但不是完整即時方案

`MessageCacheService` 負責頁面訊息快取與失效，不處理即時同步。

## 建議開發方向

下一階段優先順序：

1. 依 [`../docs/phase-2-checklist.md`](../docs/phase-2-checklist.md) 補 broadcast 測試
2. 明確定義聊天室清空權限
3. 規劃 presence / typing
4. 再評估是否升級成 private / presence channel

## 開發時的判斷原則

- 不要把 SSE 誤判成 WebSocket
- 不要忽略現在已經有 Ably 房間級同步
- 若要做 WebSocket，優先保留現有 SSE 串流，不要一次重寫整個聊天流程
- 若要做學習型低成本部署，第一版優先考慮 Ably
- 若房間仍是全域共享，不要為了技術正統性強行改回 private channel

## 常用檔案

- `routes/web.php`
- `app/Http/Controllers/ChatRoomController.php`
- `app/Services/GPTService.php`
- `app/Services/MessageCacheService.php`
- `app/Models/ChatRoom.php`
- `app/Models/Message.php`
- `resources/js/Pages/ChatRoom.vue`
- `resources/js/bootstrap.js`

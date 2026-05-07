# GPT Chat Room - AI 開發指南

這份文件給後續開發代理或開發者快速掌握目前專案狀態。內容以現有程式碼為準，若與其他舊文件衝突，以本文件和 `docs/` 為主。

## 文件入口

- 專案開發文件：[`../docs/README.md`](../docs/README.md)
- 架構說明：[`../docs/architecture.md`](../docs/architecture.md)
- 即時通訊規劃：[`../docs/realtime-websocket-plan.md`](../docs/realtime-websocket-plan.md)

## 專案定位

這是一個 Laravel 12 + Inertia.js + Vue 3 的學習專案，主題是多聊天室 AI chat app。

目前已到一個可用階段，但還沒做真正的多人即時同步。現況是：

- 有多聊天室
- 有 AI 回覆串流
- 沒有 WebSocket 廣播

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
- 清除整個聊天室訊息

### 尚未完成

- 同房多使用者即時同步
- WebSocket / Laravel Echo 整合
- presence / typing / online users
- 事件廣播與 channel 授權

## 重要實作事實

### 1. 即時方案目前是 SSE，不是 WebSocket

`ChatRoomController::sendMessageStream()` 透過 `response()->stream()` 輸出 `text/event-stream`。

前端在 `resources/js/Pages/ChatRoom.vue` 使用 `axios` 的 `onDownloadProgress` 手動解析 SSE 內容。

### 2. 多聊天室是固定主題房，不是每人自建房

`ChatRoom` model 目前實際使用的是 4 個全域主題聊天室。雖然 model 裡仍保留 `getDefaultForUser()`，但主流程優先走全域主題房。

### 3. OpenAI 模型目前寫死，但已帶最近聊天室上下文

`app/Services/GPTService.php` 目前直接使用 `gpt-5-nano`，且 controller 會把同房最近訊息整理成 conversation messages 一起送出。

### 4. 快取已存在，但不是完整即時方案

`MessageCacheService` 負責頁面訊息快取與失效，不處理即時同步。

## 建議開發方向

下一階段優先順序：

1. 補齊 `docs/realtime-websocket-plan.md` 中的 Ably + broadcasting 導入
2. 修正歷史分頁的聊天室過濾問題
3. 為前端訊息清單補事件去重邏輯
4. 再追加 presence / typing

## 開發時的判斷原則

- 不要把 SSE 誤判成 WebSocket
- 不要在文件中宣稱「已完成多人即時聊天」
- 若要做 WebSocket，優先保留現有 SSE 串流，不要一次重寫整個聊天流程
- 若要做學習型低成本部署，第一版優先考慮 Ably

## 常用檔案

- `routes/web.php`
- `app/Http/Controllers/ChatRoomController.php`
- `app/Services/GPTService.php`
- `app/Services/MessageCacheService.php`
- `app/Models/ChatRoom.php`
- `app/Models/Message.php`
- `resources/js/Pages/ChatRoom.vue`
- `resources/js/bootstrap.js`

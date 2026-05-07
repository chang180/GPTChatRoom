# 即時通訊與 WebSocket 規劃

本文件定義下一階段的即時同步方案。前提是這是學習專案，優先考慮可快速註冊、可免費驗證流程、整合 Laravel 成本低的外部服務。

## 1. 目前現況

目前專案的即時體驗其實分成兩件事：

- AI 回覆串流：已完成，使用 `POST /chat/send-message-stream` + SSE，且會附帶最近聊天室上下文
- 多使用者共享聊天室同步：尚未完成，沒有 WebSocket

這兩者不要混在一起。SSE 已足夠處理「我送出訊息後，逐字看到 AI 回覆」；WebSocket 要補的是「同房其他人也能立刻看到事件」。

## 2. 這次規劃的目標

導入 WebSocket 後，希望做到：

- A 使用者在 `work` 房發送直接訊息，B 使用者立即看到
- A 使用者在 `study` 房發問 AI，其他同房使用者至少能看到：
  - 使用者訊息已送出
  - GPT 最終回覆已完成
- 清除聊天室時，所有同房頁面同步清空
- 後續可再加上 presence 與 typing

## 3. 服務選型

### 建議方案：Ably

建議第一版使用 Ably，理由：

- Laravel 12 官方 broadcasting 文件直接列出 Ably 支援
- 可用 Laravel 的 broadcasting 機制接上
- 官方文件明確提供 Laravel / Echo 整合方向
- 官方免費方案目前可註冊且不需信用卡，適合學習專案

依官方資料，Ably Free 方案提供：

- 6,000,000 messages / month
- 200 concurrent channels
- 200 concurrent connections
- 不需信用卡

來源：

- Laravel Broadcasting Docs: https://laravel.com/docs/12.x/broadcasting
- Ably Free Plan: https://ably.com/docs/pricing/free
- Ably Pricing Overview: https://ably.com/docs/platform/pricing

### 不選 Pusher 當第一順位的原因

Pusher 也能用，而且 Laravel 整合成熟；但這個專案目標是低成本學習與保留較寬鬆的免費驗證空間，現階段 Ably 更適合先做第一版。若未來要改成 Pusher，相同的 Echo / broadcasting 抽象大致可沿用。

Pusher 官方文件可作備選參考：

- https://pusher.com/docs/channels/
- https://pusher.com/docs/channels/getting_started/javascript/

### 為什麼不先用 Laravel Reverb

Reverb 是 Laravel 官方方案，技術上很合理；但它不是「註冊一個外部免費服務帳號即可開始」的模式，仍需要自己部署或管理服務節點。這和本專案目前的學習取向不一致，所以暫不作第一版。

## 4. 架構決策

### 決策 A

保留 SSE 處理 AI token 串流，不把 token-by-token 廣播到 WebSocket。

原因：

- 實作最簡單
- 不需要把大量 token event 放進廣播通道
- 可以把 WebSocket 聚焦在共享房間狀態同步

### 決策 B

WebSocket 只同步「事件結果」，不接手 OpenAI 串流主流程。

第一版要廣播的事件：

- `MessageCreated`
- `AiReplyCompleted`
- `ChatRoomCleared`
- `UserJoinedRoom`
- `UserLeftRoom`

### 決策 C

聊天室通道以房間 slug 或 id 區分，建議使用 private channel。

建議命名：

- `private.chat-room.{chatRoomId}`

這樣未來若房間改成每位使用者可建立自定義房，通道命名還能直接沿用。

## 5. 建議實作階段

### Phase 1: 最小可用版

目標：

- 導入 Ably
- 後端能 broadcast
- 前端能訂閱房間事件
- 直接訊息與 GPT 最終訊息可以同步到其他瀏覽器

範圍：

- 安裝 Laravel broadcasting
- 設定 `BROADCAST_CONNECTION=ably`
- 新增 broadcast events
- `ChatRoom.vue` 加入 Echo 訂閱

先不要做：

- GPT token 廣播
- typing indicator
- presence 成員列表

### Phase 2: 房間狀態同步

目標：

- 房間清除事件同步
- 切換聊天室時離開舊 channel、加入新 channel
- 避免自己送出的訊息被重複插入

### Phase 3: Presence / Typing

目標：

- presence channel 顯示房內成員
- typing 提示
- 進一步 UI 優化

## 6. 具體實作建議

### 後端

1. 執行 Laravel 官方安裝流程

```bash
php artisan install:broadcasting --ably
```

2. 安裝後確認：

- `config/broadcasting.php`
- `routes/channels.php`
- `.env` / `.env.example` 的廣播設定

3. 新增事件類別，例如：

- `app/Events/MessageCreated.php`
- `app/Events/AiReplyCompleted.php`
- `app/Events/ChatRoomCleared.php`

4. 在以下時機廣播：

- `sendMessage()` 寫入 direct message 後
- `sendMessageStream()` 寫入 user message 後
- `sendMessageStream()` GPT 完整訊息落庫後
- `clearChatRoom()` 完成刪除後

### 前端

1. 安裝 client 套件

Laravel 官方文件對 Ably 提供兩條路：

- 走 Pusher 相容模式的 `laravel-echo + pusher-js`
- 走 Ably 維護的 driver

對這個專案，第一版建議先走 Pusher 相容模式，因為 Laravel 文件與範例最直接。

2. 在 `resources/js/bootstrap.js` 初始化 Echo

3. 在 `resources/js/Pages/ChatRoom.vue`：

- mount 時訂閱當前房間
- switch theme 時離開舊房間再加入新房間
- 收到 `MessageCreated` 時，把新訊息插入列表
- 收到 `AiReplyCompleted` 時，把 GPT 完整訊息插入列表
- 收到 `ChatRoomCleared` 時，清空目前房間畫面

4. 要避免重複渲染

因為送出訊息的本地頁面本來就會先插入 optimistic UI，所以收到自己廣播回來的事件時，要靠 `message.id` 或 `client message uuid` 去重。

## 7. 建議事件資料格式

### `MessageCreated`

```json
{
  "message": {
    "id": 123,
    "chat_room_id": 2,
    "user_id": 9,
    "text": "Hello",
    "sender_type": "user",
    "created_at": "2026-05-07T10:00:00.000000Z"
  }
}
```

### `AiReplyCompleted`

```json
{
  "message": {
    "id": 124,
    "chat_room_id": 2,
    "user_id": 9,
    "text": "AI final response",
    "sender_type": "gpt",
    "created_at": "2026-05-07T10:00:05.000000Z"
  }
}
```

### `ChatRoomCleared`

```json
{
  "chat_room_id": 2,
  "deleted_count": 80
}
```

## 8. 設定項目

預計會新增或確認的環境變數：

```env
BROADCAST_CONNECTION=ably
ABLY_KEY=your_ably_key
VITE_ABLY_PUBLIC_KEY=your_ably_public_key
```

如果走 Laravel 文件中的 Pusher 相容模式，還需要確認 Ably dashboard 已開啟 Pusher protocol support。

參考：

- https://laravel.com/docs/12.x/broadcasting

## 9. 目前程式碼需要順手修正的點

在導入 WebSocket 前，建議先修下面幾個基礎問題：

1. `loadMoreMessages()` 目前控制器沒有根據 theme / room 過濾，應補上聊天室參數，否則歷史分頁可能串房。
2. `ChatRoom.vue` 目前用前端 optimistic append + 後端落庫，未來接廣播後一定要補去重策略。
3. `clearChatRoom()` 是全域刪房訊息，這是產品決策，不是 bug，但文件要明確寫清楚。

## 10. 建議結論

這個專案的下一步不要把 SSE 改成 WebSocket，而是：

1. 保留 SSE 做 AI 串流
2. 導入 Ably 做房間級事件同步
3. 先完成最小可用版事件廣播
4. 再擴充 presence 與 typing

這樣變更最小，也最符合目前專案的學習目標。

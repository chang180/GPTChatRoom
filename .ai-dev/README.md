# GPT Chat Room - AI 開發指南

這份文件給後續開發代理或開發者快速掌握目前專案狀態。內容以現有程式碼為準，若與其他舊文件衝突，以本文件和 `docs/` 為主。

## 文件入口

- **Agent 總索引（套件版本 / 規範）：** [`../AGENTS.md`](../AGENTS.md) →  canonical [`.cursor/rules/laravel-boost.mdc`](../.cursor/rules/laravel-boost.mdc)
- **分 phase 實作（private-room）：** [`.ai-dev/private-room/handoff.md`](private-room/handoff.md)（Phase 1–5 已完成，見 `progress.md`）
- 專案開發文件：[`../docs/README.md`](../docs/README.md)
- **生產佈署提醒**：[`../docs/deployment.md`](../docs/deployment.md)（migration、Google OAuth、**Reverb 即時廣播**；本機預設 `BROADCAST_CONNECTION=log`）
- 架構說明：[`../docs/architecture.md`](../docs/architecture.md)
- 即時通訊規劃：[`../docs/realtime-websocket-plan.md`](../docs/realtime-websocket-plan.md)

## 專案定位

這是一個 Laravel 13 + Inertia.js + Vue 3 的學習專案，主題是多聊天室 AI chat app。

目前已到一個可用階段，且已完成第一版多人即時同步。現況是：

- 四個公開主題房 + 邀請制私人小群組房
- 有 AI 回覆串流（SSE）
- 有 Reverb 房間級事件同步（主題 public channel、私人 private channel）

## 真實功能狀態

### 已完成

- Jetstream 認證與驗證流程
- **Google OAuth**：Socialite 註冊／登入、設定頁綁定／解除；**本機 `APP_ENV=local` 強制關閉**（ADR-007，登入頁顯示說明）。真實 Google 端到端請於**已佈署環境**依 [`docs/deployment.md`](../docs/deployment.md) § 佈署後 Google 驗證手動確認。
- 4 個固定主題聊天室：`work`、`study`、`creative`、`daily`（`global_theme`，公開頻道）
- **邀請制私人聊天室**（`private_group`）：owner 建立、複製邀請連結、登入後 accept、最多 20 人；`ChatRoomPolicy` + `chat_room_members` / `chat_room_invitations`
- 前端 **`ChatSidebar`**：主題區 + 私人房列表、建立房／邀請 modal；`ChatRoom.vue` 依 `roomMode` 使用 `Echo.private()` 或 `Echo.channel()`
- Dashboard / Welcome / AppLayout 三入口（公開聊天、私人聊天、設定）
- 訊息持久化、歷史分頁、direct / AI 模式、GPT SSE 串流、對話小結切點、清除聊天室（權限依房型：主題房規則 vs 私人房僅 owner）
- Reverb WebSocket + Laravel Echo；多瀏覽器房間同步（**佇署必設 Reverb**；本機預設 log 不同步，見 `deployment.md` § Reverb）

### 尚未完成

- presence / typing / online users
- 私人房完整成員管理 UI（後端已有 `chat.private.members.destroy`，前端僅顯示人數）
- `max_uses` 邀請次數欄位尚未強制

## 重要實作事實

### 1. 即時方案目前是 SSE + WebSocket 混合

`ChatRoomController::sendMessageStream()` 透過 `response()->stream()` 輸出 `text/event-stream`。

前端在 `resources/js/Pages/ChatRoom.vue` 使用 `axios` 的 `onDownloadProgress` 手動解析 SSE 內容。

房間級共享事件則透過 Reverb + Laravel Echo 同步。

### 2. 多聊天室：四主題房 + 私人小群組

主流程使用 4 個全域主題聊天室（slug：`work` / `study` / `creative` / `daily`）。另支援使用者建立的 **`private_group`** 邀請制小群組（`PrivateChatRoomController`），與主題房並存。Model 仍保留 `getDefaultForUser()` 等舊 API，新功能以 `type` + Policy 為準。

### 3. OpenAI 模型目前寫死，上下文含小結切點

`app/Services/GPTService.php` 使用 `gpt-5-nano`。`ConversationContextService` 在 overflow ≥ 5 則時以 `afterResponse` 非同步小結（`config/conversation.php`），聊天請求只帶「截斷後小結 + 最近 20 則」。佈署時見 [`../docs/deployment.md`](../docs/deployment.md)。

### 4. 即時廣播：主題 public、私人 private

| 房型 | `ChatRoom::type` | Laravel 廣播 | 前端 Echo | `routes/channels.php` |
|------|------------------|--------------|-----------|------------------------|
| 主題房 | `global_theme` | `Channel` `chat-room.{id}` | `Echo.channel()` | 任何登入者可訂閱 |
| 私人房 | `private_group` | `PrivateChannel` 同名 | `Echo.private()` | 僅 `chat_room_members` 成員 |

私人房發訊／load-more／clear 以 query/body **`room`**（房 id）識別；主題房仍以 **`theme`**（slug）。見 `ChatRoomController::resolveRoomForAction()`。

### 5. 快取已存在，但不是完整即時方案

`MessageCacheService` 負責頁面訊息快取與失效，不處理即時同步。

### 6. 舊版 `ChatRoomClient.vue`

倉庫內**已無**此檔；聊天唯一 Inertia 頁為 `ChatRoom.vue`。部分根目錄舊 markdown 仍提及該檔，可忽略。

## 建議開發方向

下一階段優先順序：

1. 佇署環境：Google OAuth + Ably 多人同步端到端（見 `docs/deployment.md`）
2. 依 [`../docs/phase-2-checklist.md`](../docs/phase-2-checklist.md) 補 broadcast 整合測試（可選）
3. presence / typing（需 presence channel 設計）
4. 私人房成員管理 UI（移除成員等）

## 開發時的判斷原則

- 不要把 SSE 誤判成 WebSocket
- 不要忽略現在已經有 Ably 房間級同步
- 若要做 WebSocket，優先保留現有 SSE 串流，不要一次重寫整個聊天流程
- **主題房**維持 public channel；**私人房**必須 private channel + Policy，勿混用
- 若要做學習型低成本部署，第一版優先考慮 Ably

## 常用檔案

- `routes/web.php`、`routes/channels.php`
- `app/Http/Controllers/ChatRoomController.php`
- `app/Http/Controllers/PrivateChatRoomController.php`
- `app/Http/Controllers/GoogleAuthController.php`
- `app/Policies/ChatRoomPolicy.php`
- `app/Services/GPTService.php`
- `app/Models/ChatRoom.php`
- `resources/js/Pages/ChatRoom.vue`
- `resources/js/Components/ChatSidebar.vue`
- `resources/js/bootstrap.js`

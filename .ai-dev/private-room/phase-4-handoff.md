# Phase 4 Handoff: 前端導覽與私人房 UI

> **範圍：** 僅 Phase 4（Vue / Inertia UI）。完成後更新 [`progress.md`](progress.md) 並 **STOP**。  
> **規格：** [`plan.md`](plan.md) § Phase 4。  
> **前置：** Phase 3 ✅（後端 API、`roomMode` / `privateRooms` / `members` / `canClear` props 已就緒）。  
> **下一階段：** `phase-5-handoff.md` 待 Phase 4 驗收後提供（整合驗證與文件）。

---

## 給執行 agent 的任務說明（可整段複製）

```text
你在 GPTChatRoom 專案執行 private-room 的 Phase 4 ONLY（前端與導覽）。

必讀：
- .ai-dev/private-room/phase-4-handoff.md（本檔）
- .ai-dev/private-room/plan.md（Phase 4）
- Phase 3 後端：PrivateChatRoomController、ChatRoomController resolveRoomForAction（room 參數）

硬性規則：
1. 接線 Phase 3 已提供的 Inertia props；必要時微調後端 props，但不要重做 migration / Policy / 私人房 API。
2. 私人房即時訂閱必須用 Echo.private()；主題房維持 Echo.channel()（ADR-003）。
3. 私人房發訊／load-more／clear 必須帶 room={currentChatRoom.id}，勿再用 theme slug。
4. canClear 以後端 props.canClear 為準，刪除僅靠前端猜測的 canClearCurrentChatRoom。
5. 完成 Success Criteria 後更新 progress.md 並 STOP；vendor/bin/pint --dirty（若有 PHP 小改）、php artisan test、npm run build。
6. 不要實作 Phase 5 文件大改或全新 E2E 框架。

交付物：可用的雙區聊天導覽 + 私人房建立/邀請/切換 + Dashboard/Welcome/AppLayout 改版。
```

---

## 進入條件

- [ ] `progress.md`：**Phase 3 ✅** 且 **Phase 3 Review ✅ PASS**
- [ ] 人類指派「執行 Phase 4」
- [ ] 本機已 `php artisan migrate`（含 Phase 3 三個 migration）

---

## Phase 3 已提供的後端契約（勿重造）

### Inertia props（`ChatRoom.vue`）

| Prop | 來源 | 說明 |
|------|------|------|
| `roomMode` | `'theme'` \| `'private'` | 決定 Echo 與 API 參數 |
| `themes` | 四主題列表 | 與現有相同 |
| `privateRooms` | 使用者可進的私人房 `{ id, name, slug, members_count, ... }` | index/show 有 |
| `currentChatRoom` | 目前房 | 私人房主題房皆可能有 |
| `members` | 私人房成員列表 | show 時 |
| `canClear` | bool | **取代**前端 `canClearCurrentChatRoom` 推測 |
| `messages` / `pagination` | 與現有相同 | |

### 路由（Ziggy）

| 名稱 | 用途 |
|------|------|
| `chat.index` / `chat.theme` | 公開主題房 |
| `chat.private.index` | 私人房列表（可空 messages） |
| `chat.private.show` | 進入私人房 `{chatRoom}` |
| `chat.private.store` | POST 建立房 |
| `chat.private.invitations.store` | POST 產生邀請 → JSON `accept_url` |
| `chat.invitations.accept` | POST 接受（通常分享連結給使用者開啟） |
| `chat.send-message` / `chat.send-message-stream` / `chat.load-more` / `chat.clear` | 加 query/body **`room`** = 私人房 **id**（數字） |

### 邀請 API 回應範例

```json
{
  "token": "...",
  "expires_at": "...",
  "accept_url": "https://your-app.test/chat/invitations/{token}/accept"
}
```

前端 modal：**複製 `accept_url`** 即可（第一版不做 SMTP）。

---

## 實作步驟（建議順序）

| # | 任務 | 檔案 |
|---|------|------|
| 1 | 擴充 `ChatRoom.vue` props：`roomMode`, `privateRooms`, `members`, `canClear` | `ChatRoom.vue` |
| 2 | 抽出 **`ChatSidebar.vue`**：區塊 A 四主題、區塊 B 私人房 +「建立新房」 | 新元件 |
| 3 | `subscribeToChatRoom`：`roomMode==='private'` → `Echo.private(channelName)`，否則 `Echo.channel`；切房時 `leave`/`stopListening` | `ChatRoom.vue` |
| 4 | 發訊／stream／load-more／clear：私人房傳 `room: currentChatRoom.id`，主題房傳 `theme: slug` | `ChatRoom.vue` |
| 5 | 建立私人房：表單 POST `route('chat.private.store')`（Inertia `router.post` 或 form） | sidebar modal |
| 6 | 邀請：POST `route('chat.private.invitations.store', roomId)` → 顯示/複製 `accept_url` | modal |
| 7 | 切換：主題 `router.visit(chat.theme)`；私人 `router.visit(chat.private.show, id)` | sidebar |
| 8 | **`AppLayout.vue`**：加「私人聊天」NavLink；`chat.index`/`chat.theme`/`chat.private.*` 高亮 | `AppLayout.vue` |
| 9 | **`Dashboard.vue`**：卡片入口—公開聊天、私人房列表、設定 | `Dashboard.vue` |
| 10 | **`Pages/Welcome.vue`**：若有 `route('chat')` 改 `chat.index`；CTA 區分公開 vs 登入後私人房 | `Welcome.vue` |
| 11 | （可選）`Components/Welcome.vue` 與 Dashboard 文案對齊 | |
| 12 | `npm run build`；手動：主題房雙瀏覽器仍同步、私人房兩帳號+邀請 | |
| 13 | 若僅前端變更，PHP 測試應仍全綠；可加 1–2 個 Inertia assert 測試（可選） | |

### 禁止（Phase 4）

- 新 migration、改 Policy 業務規則（除非 props 缺欄位且必要）
- 改 SSE 為 WebSocket
- Phase 5 才做的全專案 README 大更新（可記在 progress Issues）
- `ChatRoomClient.vue` 若已棄用可不改，除非專案仍在用

---

## `ChatSidebar.vue` 建議結構

```
┌─ 公開主題 ─────────────┐
│ 工作 / 學習 / 創意 / 日常 │  → switchTheme(slug)
├─ 私人聊天室 ───────────┤
│  + 建立新房             │  → modal → POST chat.private.store
│  • 房間 A (3人)         │  → visit chat.private.show
│  • 房間 B               │
└────────────────────────┘
```

- 當前房：主題比 slug；私人比 `currentChatRoom.id`
- `privateRooms` 空陣列時顯示引導建立

---

## Echo 訂閱（關鍵）

現況（需改）：[`ChatRoom.vue`](../../resources/js/Pages/ChatRoom.vue) `subscribeToChatRoom` 一律 `getEcho().channel(channelName)`。

目標邏輯：

```javascript
const channelName = `chat-room.${chatRoom.id}`
// 離開舊 channel（public 與 private API 不同，需分別 leave）
if (roomMode === 'private') {
  getEcho().private(channelName).listen(...)
} else {
  getEcho().channel(channelName).listen(...)
}
```

切換房間或 `onUnmounted` 時記得取消訂閱，避免重複 listener。

**驗證：** 兩使用者同私人房，一方發 direct message，另一方即時出現（需 Ably 與 sanctum 認證正常）。

---

## 發訊參數對照

| roomMode | send-message / stream | load-more | clear |
|----------|----------------------|-----------|-------|
| `theme` | `theme: slug` | `theme` | `theme` |
| `private` | `room: id` | `room: id` | `room: id` |

`ChatRoomController::resolveRoomForAction` 已實作；前端必須配合。

---

## Dashboard / Welcome / AppLayout

### Dashboard（[`Dashboard.vue`](../../resources/js/Pages/Dashboard.vue)）

建議三張卡片（Tailwind + dark mode，與現有風格一致）：

1. **公開主題聊天** → `route('chat.index')` 或預設 `chat.theme` work  
2. **私人聊天室** → `route('chat.private.index')`  
3. **帳號設定** → `route('profile.show')`  

可精簡或取代 [`Components/Welcome.vue`](../../resources/js/Components/Welcome.vue) 在 Dashboard 內過長的重複內容（保留必要說明即可）。

### Welcome（[`Pages/Welcome.vue`](../../resources/js/Pages/Welcome.vue)）

- 修正 `route('chat')` → `route('chat.index')`（若仍存在）
- 訪客：強調四主題公開房；登入後可建立私人房（文案即可）

### AppLayout（[`AppLayout.vue`](../../resources/js/Layouts/AppLayout.vue)）

```vue
<NavLink :href="route('chat.private.index')" :active="route().current('chat.private.*')" />
```

（依 Ziggy 版本調整 `current()` 寫法；至少 `chat.private.index` / `chat.private.show` 高亮。）

---

## Success Criteria（與 plan.md 對齊）

- [ ] 從 Dashboard 可進公開房與私人房列表
- [ ] 房內可切換主題／私人房，訊息與即時不 broken
- [ ] 私人房可建立、產生邀請連結、第二帳號 accept 後可聊天（手動）
- [ ] 私人房使用 `Echo.private`；主題房仍 public channel
- [ ] `npm run build` 成功
- [ ] `php artisan test` 全綠

---

## 驗證指令

```bash
npm run build
php artisan test
```

### 手動清單（建議寫入 progress Verification）

1. 帳號 A：Dashboard → 建立私人房 → 產生邀請連結  
2. 帳號 B：開啟 `accept_url`（已登入）→ 進入同房  
3. B 發 direct message → A 即時看到  
4. 切換至「工作」主題房 → 仍為公開房同步  
5. 非成員 C 無法從 URL 進入私人房（403）

---

## progress.md 回寫（完成 Phase 4 後必做）

### 1. 階段 B 表格

```markdown
| Phase 4 前端導覽 | ✅ 完成 | YYYY-MM-DD | 見下方 Phase 4 執行回報 |
```

### 2. 新增 `## Phase 4 執行回報`

含：Success Criteria、執行摘要、Files Changed、Verification（含手動）、Deviations、Issues 留待 Phase 5、Review 檢查點。

### 3. Review 檢查點

- [ ] Echo.private 僅私人房
- [ ] API 參數 room vs theme 正確
- [ ] 未重做 Phase 3 後端
- [ ] Welcome/Dashboard/AppLayout 已改版

### 4. Next Steps → 等待 `phase-5-handoff.md`

---

## 給 review agent 的驗收提示（Phase 4）

```text
Review GPTChatRoom private-room Phase 4 only.

輸入：plan.md Phase 4、phase-4-handoff.md、progress.md、git diff

檢查：
1. ChatRoom 私人房與主題房切換、Echo、room 參數？
2. 邀請 UI 與建立房？
3. Dashboard/Welcome/AppLayout？
4. 無 Phase 3 後端重寫？

輸出：Issues、Fixes required、Final verdict: PASS / NEEDS_CHANGES
```

---

## 完成後 STOP

- 不要執行 Phase 5（全專案驗收與 README 狀態更新屬 Phase 5）
- 不要撰寫 `phase-5-handoff.md`（由規劃端提供）

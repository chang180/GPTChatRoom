# Phase 3 Handoff: 邀請制私人聊天室（後端）

> **範圍：** 僅 Phase 3（後端 + 廣播 + 最小 Inertia props）。完成後更新 [`progress.md`](progress.md) 並 **STOP**。  
> **規格：** [`plan.md`](plan.md) § Phase 3、[`decisions.md`](decisions.md) ADR-002、ADR-003、ADR-005。  
> **前置：** Phase 1 ✅、Phase 2 ✅（Google OAuth；佇署 E2E 由人類上線後驗證，**非本 Phase 阻擋條件**）。  
> **下一階段：** `phase-4-handoff.md` 待 Phase 3 驗收後提供（前端導覽與 ChatRoom UI）。

---

## 給執行 agent 的任務說明（可整段複製）

```text
你在 GPTChatRoom 專案執行 private-room 的 Phase 3 ONLY（私人房後端）。

必讀：
- .ai-dev/private-room/phase-3-handoff.md（本檔，含 progress 回寫格式）
- .ai-dev/private-room/plan.md（Phase 3 章節）
- .ai-dev/private-room/decisions.md（ADR-002 邀請制、ADR-003 public/private channel、ADR-005 成員上限）

硬性規則：
1. 只做私人房後端：migration、Model、Policy、路由、Controller、廣播 PrivateChannel、測試。
2. 可做最小 Inertia 回傳（例如私人房列表 API 或簡單 index 頁 props），但不做 Phase 4 的 ChatSidebar、Welcome/Dashboard 大改版。
3. 四主題房維持 public Channel；僅 private_group 用 PrivateChannel（ADR-003）。
4. 不要改 SSE 串流邏輯；訊息 send/stream/clear 沿用 ChatRoomController，抽出 resolve 分岐。
5. 完成 Success Criteria 後更新 progress.md 並 STOP；執行 vendor/bin/pint --dirty、php artisan test。
6. 開始前列出預計新增/修改的檔案。

交付物：可測的私人房 API + Policy + 廣播授權 + Feature tests（不含完整前端 UI）。
```

---

## 進入條件

- [ ] `progress.md`：**Phase 2 ✅** 且 **Phase 2 Review ✅ PASS**
- [ ] 人類指派「執行 Phase 3」或「依 phase-3-handoff 實作私人房後端」
- [ ] 勿與 Phase 4 混做（雙區導覽、Welcome 改版屬 Phase 4）

---

## 產品範圍（ADR-002）

| 要做 | 不做 |
|------|------|
| 邀請制 `private_group` 小群組 | 1:1 私訊、個人 solo 房 |
| owner 建房、產生邀請連結、成員 accept | SMTP 寄信邀請 |
| 成員可發訊／AI（沿用現有 API） | presence / typing |
| 非成員 403 | 主題房改 private channel |

**預設（ADR-005）：** 僅**已登入**者可 accept；每房最多 **20** 人（含 owner）；邀請 token 建議 7 天有效。

---

## 實作步驟（依序）

| # | 動作 | 驗證 |
|---|------|------|
| 1 | Migration：`chat_rooms.type`、`created_by`；表 `chat_room_members`、`chat_room_invitations` | `php artisan migrate` |
| 2 | 既有主題房 backfill `type = global_theme`（migration 或 seeder） | 四主題仍正常 |
| 3 | Models：`ChatRoomMember`、`ChatRoomInvitation`；擴充 `ChatRoom` | 關聯與 helper 方法 |
| 4 | `ChatRoomPolicy` + `AuthServiceProvider` 註冊 | `authorize()` 可用 |
| 5 | `ChatRoomResolver` 或 `ResolvesChatRoom` trait | 主題 slug 與私人房 id 分離 |
| 6 | `PrivateChatRoomController`（或擴充路由群組） | 路由命名清晰 |
| 7 | 調整 `ChatRoomController`：resolve + `canClear` 對私人房 | 既有主題測試仍過 |
| 8 | `BroadcastsToChatRoom`（或 event 內分支）+ 三個 Event 更新 | `broadcastOn()` 測試 |
| 9 | `routes/channels.php` 成員檢查 | 私人房僅成員可訂閱 |
| 10 | `tests/Feature/PrivateChatRoomTest.php` | 全綠 |
| 11 | 可選：最小 `GET /chat/private` Inertia（列表 props only） | 手動或測試 assertInertia |
| 12 | `vendor/bin/pint --dirty` → `php artisan test` | 全套通過 |

### 禁止（Phase 3）

- `ChatSidebar.vue`、Welcome/Dashboard 大改（Phase 4）
- 修改 `GoogleAuthController` / OAuth
- 將 4 主題房改 `PrivateChannel`
- Phase 4 的 `Echo.private()` 前端訂閱（可留註解；後端 channel 須先就緒）

---

## 資料模型

### `chat_rooms` 新增欄位

| 欄位 | 說明 |
|------|------|
| `type` | `global_theme` \| `private_group`（建議 string + 常數或 enum） |
| `created_by` | `foreignId` → `users.id`（私人房 owner；主題房可 null） |

既有主題：`user_id = null`，`slug` ∈ work/study/creative/daily。

私人房：`slug` 唯一（例如 `private-{uuid}` 或 `private-{id}`），**不可**與主題 slug 衝突。

### `chat_room_members`

- `chat_room_id`, `user_id`, `role`（`owner` | `member`）, `joined_at`
- `unique(chat_room_id, user_id)`
- 建立房時自動 insert owner

### `chat_room_invitations`

- `chat_room_id`, `token`（unique）, `invited_by`, `expires_at`
- `accepted_at`, `accepted_by` nullable
- `revoked_at` nullable；可選 `max_uses`

---

## `ChatRoom` 模型方法（建議）

```php
public function isGlobalTheme(): bool  // 已有；可改為 type === global_theme
public function isPrivateGroup(): bool
public function hasMember(User $user): bool
public function members(): BelongsToMany
```

---

## 路由（建議命名）

| 方法 | URI | 名稱建議 | Policy |
|------|-----|----------|--------|
| GET | `/chat/private` | `chat.private.index` | 登入；回傳使用者可進的私人房列表 |
| GET | `/chat/private/{chatRoom}` | `chat.private.show` | `view` |
| POST | `/chat/private` | `chat.private.store` | 登入即可建房的規則（建立者=owner） |
| POST | `/chat/private/{chatRoom}/invitations` | `chat.private.invitations.store` | `invite` |
| POST | `/chat/invitations/{token}/accept` | `chat.invitations.accept` | 登入 + token 有效 |
| DELETE | `/chat/private/{chatRoom}/members/{user}` | `chat.private.members.destroy` | `removeMember` |

**現有主題路由** `/chat`、`/chat/{theme}`：`resolveChatRoom` **僅**接受 `GLOBAL_THEME_DEFINITIONS` 的 slug；`{theme}` 不得與私人房 id 混淆（建議私人房用 route model binding 於 `/chat/private/{chatRoom}`）。

**訊息 API**（沿用，加 `chat_room_id` 或 `room` 參數）：

- `POST /chat/send-message`、`POST /chat/send-message-stream`、`GET /chat/load-more`、`DELETE /chat/clear`
- resolve 時若指定私人房 id → Policy `sendMessage` / `clear`

---

## `ChatRoomController` 重構要點

現有檔案：[`app/Http/Controllers/ChatRoomController.php`](../../app/Http/Controllers/ChatRoomController.php)

- `resolveChatRoom()`（約 L316）：拆成
  - `resolveGlobalTheme(string $slug): ChatRoom`
  - `resolvePrivateRoom(User $user, ChatRoom|int $room): ChatRoom` + `$this->authorize('view', $room)`
- `canClearChatRoom()`（約 L331）：主題房仍 **false**；私人房僅 **owner** 可 clear（對齊 ADR）

Inertia props（最小，供 Phase 4 接線）：

```php
'roomMode' => 'theme' | 'private',
'canClear' => bool,  // 後端計算，勿只靠前端
// 私人房頁可加：'members' => ..., 'privateRooms' => ...（index）
```

---

## 廣播（ADR-003）

### Helper 概念

```php
// 伪代码 — 實作時放 concern 或 ChatRoom::broadcastChannel()
if ($room->isGlobalTheme()) {
    return [new Channel("chat-room.{$room->id}")];
}
return [new PrivateChannel("chat-room.{$room->id}")];
```

更新：

- [`app/Events/ChatMessageCreated.php`](../../app/Events/ChatMessageCreated.php)
- [`app/Events/AiReplyCompleted.php`](../../app/Events/AiReplyCompleted.php)
- [`app/Events/ChatRoomCleared.php`](../../app/Events/ChatRoomCleared.php)

### `routes/channels.php`

```php
Broadcast::channel('chat-room.{chatRoomId}', function ($user, $chatRoomId) {
    $room = ChatRoom::find($chatRoomId);
    if (! $room) {
        return false;
    }
    if ($room->isGlobalTheme()) {
        return $user !== null;
    }
    return $room->hasMember($user);
});
```

**Phase 3 不要求**改 `resources/js/bootstrap.js` 的 `Echo.private()`（Phase 4）；但 channel 授權必須正確，否則 Phase 4 即時會失敗。

---

## Policy：`ChatRoomPolicy`

| 能力 | 規則 |
|------|------|
| `view` | 成員 |
| `sendMessage` | 成員 |
| `clear` | 私人房 owner；主題房 deny |
| `invite` | owner |
| `removeMember` | owner；不可移除自己若為唯一 owner |
| `delete` | owner（若實作刪房） |

使用 Form Request 驗證建立房名稱、邀請等。

---

## 測試：`tests/Feature/PrivateChatRoomTest.php`

| 測試 | 說明 |
|------|------|
| 非成員 GET 私人房 | 403 |
| owner 建立房 + 成員在 pivot | 201/redirect |
| 產生邀請 token | 201 + token 結構 |
| accept（已登入） | 成員新增 |
| token 過期 / 已撤銷 | 422/403 |
| 滿 20 人 accept | 拒絕 |
| 非成員 POST 訊息到該房 | 403 |
| `ChatMessageCreated` on private room | `broadcastOn` 含 `PrivateChannel` |
| 主題房事件 | 仍為 `Channel`（public） |

使用 factory；`actingAs` 兩個 user 測邀請。

---

## Success Criteria（與 plan.md 對齊）

- [ ] 成員 / 邀請 / accept 流程測試通過
- [ ] 非成員 403
- [ ] 私人房廣播 `PrivateChannel`；主題房仍 `Channel`
- [ ] `php artisan test` 全綠
- [ ] 無 Phase 4 專屬 UI 檔案（除非極小 index props 頁）

---

## 驗證指令

```bash
php artisan migrate
vendor/bin/pint --dirty
php artisan test tests/Feature/PrivateChatRoomTest.php
php artisan test
```

---

## progress.md 回寫（完成 Phase 3 後必做）

### 1. 階段 B 表格

```markdown
| Phase 3 私人房後端 | ✅ 完成 | YYYY-MM-DD | 見下方 Phase 3 執行回報 |
```

### 2. 新增 `## Phase 3 執行回報`

含：Success Criteria 勾選、執行摘要、Files Changed、Verification（test 輸出）、Deviations、Issues 留待 Phase 4、Review 檢查點。

### 3. Review 檢查點（給 review agent）

- [ ] 無 Phase 4 UI（ChatSidebar、Welcome 大改）
- [ ] 主題房行為未破壞（既有 ChatRoom 測試）
- [ ] PrivateChannel + channels.php 成員檢查
- [ ] 邀請制符合 ADR-002/005

### 4. `## Next Steps`

指向 `phase-4-handoff.md`；勿開始 Phase 4。

---

## 給 review agent 的驗收提示（Phase 3）

```text
Review GPTChatRoom private-room Phase 3 only.

輸入：plan.md Phase 3、phase-3-handoff.md、progress.md Phase 3 執行回報、git diff

檢查：
1. progress 回寫完整？
2. 僅後端 + 廣播，無 Phase 4 前端大改？
3. 主題房 public、私人房 private？
4. Policy / 邀請 / 403 測試足夠？

輸出：Issues、Fixes required、Final verdict: PASS / NEEDS_CHANGES
```

---

## 完成後 STOP

- 不要撰寫 `phase-4-handoff.md`（由規劃端提供）
- 不要改 `ChatRoom.vue` 雙區導覽（除非最小 props 接線且已寫入 Deviations）

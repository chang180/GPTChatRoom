# Decisions: private-room

記錄本功能相關的架構與產品決策（ADR）。規格以本目錄 `plan.md` 為準。

---

## ADR-001：工作治理 — 規格 / 分 phase 實作 / Review 分離

**狀態：** 已接受  
**日期：** 2026-06-01

**背景：** 單次 agent 若一次執行 Phase 1–5，會吞掉整個專案且難以 review。

**決策：**

| 階段 | 產出 | 執行者 |
|------|------|--------|
| A | `.ai-dev/private-room/*.md` 規格檔 | 規劃 agent |
| B | 每次**一個** Phase 的程式變更 | Codex / Claude / 另開 Cursor 任務 |
| C | Review 報告（PASS / NEEDS_CHANGES） | 另開 Cursor review 任務 |

**後果：** 實作 agent 必須讀 `handoff.md` 的 STOP 規則；未被人類指派不得進入下一 Phase。

---

## ADR-002：私人聊天室 = 邀請制小群組

**狀態：** 已接受

**決策：**

- **要做：** 建立者可邀請成員的 `private_group` 房；owner / member 角色。
- **不做（本專案第一版）：** 個人 solo 房（僅本人）、1:1 私訊。

**後果：** 需要 `chat_room_members`、`chat_room_invitations`；授權以成員為準。

---

## ADR-003：四主題房維持 public channel

**狀態：** 已接受

**決策：** `work` / `study` / `creative` / `daily` 仍為全域共享、`user_id = null`，Ably 繼續用 **public** `chat-room.{id}`。

**後果：** 僅 `private_group` 使用 `PrivateChannel` + `routes/channels.php` 成員檢查。

---

## ADR-004：Google 帳號 — 同 email 綁定合併

**狀態：** 已接受

**決策：**

- Google callback 若 email 已存在且尚未綁定 `google_id` → **不建立新使用者**；提示登入後至設定頁綁定（或經密碼確認的一次性綁定流程）。
- 已登入使用者可 `link` / `unlink`；unlink 前須保留密碼或其他可登入方式。

**後果：** `users.password` 需允許 nullable；需 Feature tests 覆蓋衝突情境。

---

## ADR-007：Google OAuth 僅在已佈署環境啟用

**狀態：** 已接受  
**日期：** 2026-06-01（規格修訂）

**背景：** Google OAuth 需於 Google Cloud Console 設定授權重新導向 URI，通常僅能對應已佈署網域；本機（`local`）難以穩定完成端到端測試，也不應誤導開發者點擊後失敗。

**決策：**

| 環境 | Google OAuth |
|------|----------------|
| `APP_ENV=local`（本機） | **強制關閉**（即使 `.env` 設了 `GOOGLE_OAUTH_ENABLED=true` 也無效） |
| 已佈署（`production` / `staging` 等，非 `local`） | 依 `GOOGLE_OAUTH_ENABLED` + 已設定 `GOOGLE_CLIENT_*` 決定是否啟用 |

**設定（實作 Phase 2）：**

- `config/services.php` 新增 `google.enabled`（由 config 計算，**勿**在 controller 直接 `env()`）
- `.env.example`：`GOOGLE_OAUTH_ENABLED=false`（本機預設）；佈署環境文件註明改為 `true` 並填入憑證
- 後端：`redirect` / `callback` / `link` 在未啟用時回 **404** 或 **403**（擇一並寫入測試）
- 前端：Login、Register、Profile 的 Google 區塊改為**停用按鈕 + 頁面內說明文字**（非僅隱藏元素）
- Inertia：透過 shared prop 或各頁 props 傳 `googleOAuthEnabled`（bool）與可選 `googleOAuthDisabledReason`（字串）

**頁面說明文案（繁中，可調整措辭但需保留意涵）：**

> Google 登入／註冊僅在已佈署環境提供。本機開發請使用電子郵件與密碼；若需測試 Google 流程，請於 staging／production 環境操作。

**測試：**

- OAuth 流程 Feature test 在 `config(['services.google.enabled' => true])` 且非 `local` 環境下執行（可用 `withoutMiddleware` 或 `$this->app['env']` 覆寫）
- 另加測試：`local` + 未啟用時 `GET auth/google/redirect` 為 404/403；前端 prop 為 `false`

**後果：** Phase 5 手動驗收清單的 Google 項目標註「僅佈署環境」；本機以 email／password 驗證其他功能即可。

---

## ADR-005：邀請連結與成員上限（第一版預設）

**狀態：** 已接受（建議預設，實作 Phase 3 時採用）

| 項目 | 預設 |
|------|------|
| 接受邀請 | **僅已登入**使用者可 `accept` |
| 成員上限 | **20** 人 / 房（含 owner） |
| 邀請交付 | **複製連結**；不做 SMTP 寄信 |
| Token | 隨機字串；`expires_at`（建議 7 天）；可 `revoked_at` |

---

## ADR-006：保留 SSE + 混合即時架構

**狀態：** 已接受

**決策：** AI 回覆仍走 `sendMessageStream` SSE；房間級事件仍 Ably。不把 token 串流改 WebSocket。

**參考：** `.ai-dev/README.md`、`docs/realtime-websocket-plan.md`

---

## 環境盤點（規格產出時唯讀，2026-06-01）

| 項目 | 值 |
|------|-----|
| Laravel | 12.35.1 |
| PHP | 8.4.21 |
| inertiajs/inertia-laravel | v2.0.10 |
| laravel/jetstream | v5.3.8 |
| laravel/sanctum | v4.2.0 |
| ably/laravel-broadcaster | v1.0.8 |
| openai-php/laravel | v0.11.0 |
| pestphp/pest | v3.8.4 |
| Broadcasting driver | ably |

### Laravel 13 升級前待驗證套件

Phase 1 執行者必須在 `composer update` 前查各套件 release notes / Packagist 對 L13 的支援：

- `laravel/jetstream` ^5.1
- `inertiajs/inertia-laravel` ^2.0
- `laravel/sanctum` ^4.0
- `ably/laravel-broadcaster` ^1.0
- `openai-php/laravel` ^0.11
- `laravel/fortify`（Jetstream 依賴）
- `pestphp/pest` ^3.0

**備註：** Laravel 13 要求 PHP ^8.3；本專案 `composer.json` 已 `^8.4`，無需降版。

---

## 變更紀錄

| 日期 | ADR | 摘要 |
|------|-----|------|
| 2026-06-01 | 001–006 | 初版規格交付 |
| 2026-06-01 | 007 | Google OAuth 本機強制關閉、佈署環境啟用 |

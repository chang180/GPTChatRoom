# Phase 2 Handoff: Google OAuth 註冊與帳號綁定

> **範圍：** 僅 Phase 2。完成後更新 [`progress.md`](progress.md) 並 **STOP**。  
> **規格：** [`plan.md`](plan.md) § Phase 2、[`decisions.md`](decisions.md) ADR-004、ADR-007。  
> **前置：** Phase 1 ✅（Laravel 13.12.0，見 progress「Phase 1 執行回報」）。  
> **下一階段：** [`phase-3-handoff.md`](phase-3-handoff.md)（私人房後端）。Google 佇署 E2E 由人類上線後依 `docs/deployment.md` 驗證，非 Phase 3 前置。

---

## 給執行 agent 的任務說明（可整段複製）

```text
你在 GPTChatRoom 專案執行 private-room 的 Phase 2 ONLY。

必讀：
- .ai-dev/private-room/phase-2-handoff.md（本檔，含 progress 回寫格式）
- .ai-dev/private-room/plan.md（Phase 2 章節）
- .ai-dev/private-room/decisions.md（ADR-004、ADR-007）

硬性規則：
1. 只做 Google OAuth（Socialite）+ 設定頁綁定；不做私人房、Welcome/Dashboard 大改版。
2. ADR-007：APP_ENV=local 強制關閉 OAuth；Login/Register/Profile 須顯示停用說明（不可只 hide 按鈕）。
3. ADR-004：Google callback 時若 email 已存在且無 google_id，不得建立新 User。
4. 完成 plan.md Phase 2 Success Criteria 後，依本檔「progress.md 回寫」更新 progress.md，然後 STOP。
5. 執行 vendor/bin/pint --dirty；php artisan test；npm run build。
6. OAuth 功能測試在「啟用環境」用 config/環境覆寫；本機 local 測「關閉」行為。

交付物：Socialite 整合 + 測試 + 已更新的 progress.md（供 review）。
```

---

## 進入條件

- [ ] `progress.md` 顯示 **Phase 1 ✅ 完成** 且 **Phase 1 Review ✅ PASS**
- [ ] 人類指派「執行 Phase 2」或「依 phase-2-handoff 實作 Google OAuth」
- [ ] Phase 2 在 progress 為 **未開始** 或 **進行中**（已完成則勿重跑，除非人類要求修復）

---

## 實作步驟（依序）

| # | 動作 | 驗證 |
|---|------|------|
| 1 | `composer require laravel/socialite` | `composer install` 成功 |
| 2 | Migration：`users` 加 `google_id`（unique nullable）、token 欄位；`password` nullable | `php artisan migrate` |
| 3 | `config/services.php` 新增 `google` + **`enabled` 邏輯（ADR-007）** | `config('services.google.enabled')` 在 local 恒 false |
| 4 | `.env.example`：`GOOGLE_OAUTH_ENABLED=false`、`GOOGLE_CLIENT_*`、`GOOGLE_REDIRECT_URI` | 有註解說明佈署環境 |
| 5 | `docs/deployment.md` 新增 § Google OAuth（Console redirect URI、佈署 env） | 文件可讀 |
| 6 | `GoogleAuthController`：`redirect`、`callback`；未 enabled 時 `abort(404)` | Feature test |
| 7 | 路由：`auth/google/redirect`、`auth/google/callback` | 見下方路由表 |
| 8 | 已登入：`POST /user/google/link`、`DELETE /user/google/unlink` + Form Request | unlink 無密碼時拒絕 |
| 9 | `HandleInertiaRequests::share`（或專用 helper）傳 `googleOAuthEnabled`、`googleOAuthDisabledReason` | Inertia test / 手動 |
| 10 | `Auth/Login.vue`、`Register.vue`：按鈕或停用說明區塊 | 本機可見說明文案 |
| 11 | `Profile/Partials/GoogleAccountForm.vue` + `Profile/Show.vue` | 綁定狀態 / link / unlink |
| 12 | `User` model：`fillable` / `hidden` 更新 | — |
| 13 | `tests/Feature/GoogleAuthTest.php` | 全綠 |
| 14 | `vendor/bin/pint --dirty` → `php artisan test` → `npm run build` | 與 Phase 1 相同門檻 |

### 禁止（Phase 2）

- 私人房 migration、`ChatRoomPolicy`、私人路由
- 修改 `ChatRoom.vue` 聊天邏輯（除非編譯錯誤）
- 在本機 `.env` 預設開啟 `GOOGLE_OAUTH_ENABLED=true`（example 維持 false）
- 開始 Phase 3

---

## `config/services.php`（ADR-007）

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
    'enabled' => ! app()->environment('local')
        && filter_var(env('GOOGLE_OAUTH_ENABLED', false), FILTER_VALIDATE_BOOL)
        && filled(env('GOOGLE_CLIENT_ID'))
        && filled(env('GOOGLE_CLIENT_SECRET')),
],
```

**`googleOAuthDisabledReason` 建議（local / 未啟用）：**

> Google 登入／註冊僅在已佈署環境提供。本機開發請使用電子郵件與密碼；若需測試 Google 流程，請於 staging／production 環境操作。

可抽成 `config` 字串或 `lang` 檔，但前後端需一致。

---

## 路由建議

| 方法 | URI | 名稱建議 | 說明 |
|------|-----|----------|------|
| GET | `/auth/google/redirect` | `auth.google.redirect` | 導向 Google |
| GET | `/auth/google/callback` | `auth.google.callback` | 處理 callback；登入或註冊 |
| POST | `/user/google/link` | `user.google.link` | 已登入綁定（middleware `auth`） |
| DELETE | `/user/google/unlink` | `user.google.unlink` | 已登入解除（需有密碼） |

註冊於 `routes/web.php`；與 Fortify 路由並存。

---

## 業務邏輯（ADR-004）

### Callback（未登入）

1. 若 `! config('services.google.enabled')` → 404。
2. Socialite 取得 Google user。
3. 若存在 `User` where `google_id` = id → 登入該使用者。
4. 若存在 `User` where `email` = email 且 `google_id` null → **不建帳**；redirect 登入頁 + flash（請先登入後至個人設定綁定 Google）。
5. 否則建立新 User（`password` null，`google_id` 等寫入）並登入。

### Link（已登入）

- 將目前帳號寫入 `google_id` / tokens；若該 `google_id` 已被其他帳號使用 → 錯誤。

### Unlink

- 若 `password` 為空 → 422 / 驗證錯誤，提示須先設定密碼。

---

## 前端要點

| 檔案 | 行為 |
|------|------|
| `resources/js/Pages/Auth/Login.vue` | `googleOAuthEnabled` ? Google 按鈕 : 灰色區塊 + `googleOAuthDisabledReason` |
| `resources/js/Pages/Auth/Register.vue` | 同上 |
| `resources/js/Pages/Profile/Partials/GoogleAccountForm.vue` | 顯示是否已綁定、link/unlink 或說明 |
| `resources/js/Pages/Profile/Show.vue` | 引入 partial |

使用 `usePage().props.googleOAuthEnabled`（若 shared）或頁面 props。

**Dark mode：** 與現有 Auth/Profile 元件一致，使用 Tailwind `dark:`。

---

## 測試策略（`tests/Feature/GoogleAuthTest.php`）

| 測試 | 環境設定 |
|------|----------|
| local 下 `GET auth/google/redirect` → 404 | 預設 `APP_ENV=local` |
| 啟用 OAuth 時 mock Socialite → 新使用者建立並登入 | `$this->app['env'] = 'staging'` 或 `config` 覆寫 + `withoutMiddleware` 視需要 |
| email 已存在、無 google_id → 不增加 User 數 | 同上 |
| 已登入 link 成功 | Sanctum actingAs |
| unlink 無密碼失敗 | actingAs 純 Google user |
| Login Inertia 頁 `googleOAuthEnabled === false` on local | `get(route('login'))->assertInertia(...)` |

**不要**在 CI 呼叫真實 Google API；一律 `Socialite::fake()` 或 mock driver。

---

## Success Criteria（與 plan.md 對齊）

- [ ] **佈署設定下**（測試用 staging + enabled + mock）新 Google 使用者可登入
- [ ] 同 email 不重複建帳
- [ ] 設定頁 link/unlink 與測試通過
- [ ] **本機 local**：`googleOAuthEnabled` false；三頁有可見說明；OAuth 路由 404
- [ ] `php artisan test` 全綠；`npm run build` 成功

---

## 驗證指令

```bash
vendor/bin/pint --dirty
php artisan test tests/Feature/GoogleAuthTest.php
php artisan test
npm run build
```

本機手動：開 Login → 應見 Google 不可用說明；訪問 `/auth/google/redirect` → 404。

佈署/staging 手動：設定 `GOOGLE_*` 與 Console redirect URI 後走完整 OAuth（由人類執行，可不寫入自動化）。

---

## progress.md 回寫（完成 Phase 2 後必做）

### 1. 階段 B 表格

```markdown
| Phase 2 Google OAuth | ✅ 完成 | YYYY-MM-DD | 見下方 Phase 2 執行回報 |
```

### 2. 新增或覆寫 `## Phase 2 執行回報`

```markdown
## Phase 2 執行回報

**執行者：**
**完成日期：**
**狀態：** ✅ 完成 | ⚠️ 部分完成

### Success Criteria

- [x] 佈署/mock 環境 Google 登入
- [x] 同 email 不重複建帳
- [x] 設定頁 link/unlink
- [x] local 關閉 + 頁面說明 + 路由 404

### 執行摘要

### Files Changed

### Verification

\`\`\`
（test 摘要）
\`\`\`

### Deviations

### Issues / 風險留待 Phase 3

### Review 檢查點

- [ ] 無私人房 / chat_room_members 等 Phase 3 檔案
- [ ] local 強制關閉（ADR-007）
- [ ] GoogleAuthTest 存在且通過
- [ ] .env.example 與 deployment.md 已更新
```

### 3. `## Completed` 新增一行

### 4. `## Next Steps` 改為等待 `phase-3-handoff.md`

---

## 給 review agent 的驗收提示（Phase 2）

```text
Review GPTChatRoom private-room Phase 2 only。

輸入：plan.md Phase 2、phase-2-handoff.md、progress.md Phase 2 執行回報、git diff

檢查：
1. progress 回寫完整？
2. ADR-007：local 關閉 + UI 說明 + 路由保護？
3. ADR-004：同 email 不建重複帳？
4. diff 無 Phase 3（私人房）？
5. 測試覆蓋啟用/停用情境？

輸出：Issues、Fixes required、Final verdict: PASS / NEEDS_CHANGES
```

---

## 完成後 STOP

- 不要撰寫或執行 Phase 3
- 向人類回報：Phase 2 完成，請 review；通過後才指派私人房後端

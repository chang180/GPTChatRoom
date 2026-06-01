# Progress: private-room

> 實作進度追蹤。每完成一個 Phase 由**執行該 Phase 的 agent** 更新本檔，然後 STOP。

---

## 階段 A — 規格產出

| 項目 | 狀態 | 日期 |
|------|------|------|
| `plan.md` | ✅ 完成 | 2026-06-01 |
| `decisions.md` | ✅ 完成 | 2026-06-01 |
| `handoff.md` | ✅ 完成 | 2026-06-01 |
| `phase-1-handoff.md` | ✅ 完成 | 2026-06-01 |
| `progress.md` | ✅ 完成 | 2026-06-01 |
| 應用程式碼變更（規格檔 only） | ✅ 完成 | 2026-06-01 |

**結論：** 規格交付完成；**階段 B Phase 1 已完成**（見下方執行回報）。

---

## 階段 B — 分 Phase 實作

| Phase | 狀態 | 完成日 | 備註 |
|-------|------|--------|------|
| Phase 0 規格 | ✅ | 2026-06-01 | 階段 A |
| Phase 1 Laravel 13 | ✅ 完成 | 2026-06-01 | 見下方 Phase 1 執行回報 |
| Phase 2 Google OAuth | ✅ 完成 | 2026-06-01 | 見下方 Phase 2 執行回報 |
| Phase 3 私人房後端 | ⏳ 未開始 | — | |
| Phase 4 前端導覽 | ⏳ 未開始 | — | |
| Phase 5 整合驗證 | ⏳ 未開始 | — | |

---

## Phase 1 執行回報

**執行者：** Claude (Claude Code)
**完成日期：** 2026-06-01
**狀態：** ✅ 完成

### Success Criteria

- [x] Laravel 13.x（實際：v13.12.0）
- [x] php artisan test 全綠（50 passed, 0 failed, 7 skipped）
- [x] npm run build 成功

### 執行摘要

升級 `laravel/framework` 至 `^13.0`（解析為 v13.12.0），並連帶將以下依賴提升到首個支援 L13 的版本：`inertiajs/inertia-laravel ^2.0 → ^3.0`、`openai-php/laravel ^0.11 → ^0.19`、`laravel/tinker ^2.9 → ^3.0`、`laravel/boost ^1.0 → ^2.0`、`pestphp/pest ^3.0 → ^4.0`、`pestphp/pest-plugin-laravel ^3.0 → ^4.0`。其餘套件（Jetstream、Fortify、Sanctum、Nightwatch、ide-helper、collision、ably 等）在既有 caret 範圍內即可解析到 L13 相容版本，毋須改 constraint。解析阻礙僅出現兩次：`laravel/tinker` v2.x 與 `inertia-laravel` v2.x 無 L13 版本，分別以 tinker ^3.0、inertia ^3.0 解除。`bootstrap/app.php` 已是 L11+ 精簡結構、無棄用 API，無需修改。測試結果與升級前基準完全一致（50 passed / 7 skipped），未出現破壞性變更；npm 依賴未變動故未執行 `npm update`，`npm run build` 直接成功。

### Files Changed

- `.ai-dev/README.md`（專案定位 Laravel 12 → 13）
- `.ai-dev/private-room/progress.md`（本回報）
- `composer.json`（framework / inertia / openai / tinker / boost / pest constraint）
- `composer.lock`（依賴解析結果）

> 註：`.cursor/mcp.json` 在本任務開始前即為 modified（與 Phase 1 無關），未由本次變更觸碰。

### Verification

```
# php artisan about
Laravel Version .............. 13.12.0
PHP Version .................. 8.4.21

# php artisan test
Tests: 7 skipped, 50 passed (150 assertions)  — 與升級前基準一致

# vendor/bin/pint --dirty
{"tool":"pint","result":"passed"}

# npm run build
✓ built in 1.63s（僅 chunk >500kB 之資訊性警告，非錯誤）
```

### Deviations

- `docs/deployment.md` 無任何 Laravel 版本敘述（內容為對話小結佈署提醒），故無可更新處；版本敘述僅 `.ai-dev/README.md` 一處需改。
- 為解析 L13，`inertia-laravel`、`tinker`、`pest`、`openai-php/laravel`、`boost` 連帶做了 major/minor 升級（皆為相依性必需，非主動大升級）。Inertia server adapter v3 與既有 `@inertiajs/vue3` v1 前端搭配：測試與 build 皆通過，未見破壞。

### Issues / 風險留待 Phase 2

- Pest 升至 v4、Inertia server 升至 v3、openai-php/client 升至 v0.19 屬跨 major 變更；現有測試全綠，但 Phase 2+ 新增測試/串流邏輯時建議留意這些套件的行為差異。

### Review 檢查點（給 review agent）

- [x] composer.lock 中 laravel/framework 為 13.x（v13.12.0）
- [x] 測試全綠證據在 Verification
- [x] 未含 Phase 2+ 程式（無 Socialite、私人房 migration、OAuth UI）
- [x] README / deployment 版本已更新（deployment 無版本敘述，見 Deviations）

---

## Phase 2 執行回報

**執行者：** Claude (Claude Code)
**完成日期：** 2026-06-01
**狀態：** ✅ 完成

### Success Criteria

- [x] 佈署/mock 環境 Google 登入（mock Socialite + `config(services.google.enabled=true)` → 新使用者建立並登入）
- [x] 同 email 不重複建帳（ADR-004；既有 email 無 google_id → 不建帳、導回登入）
- [x] 設定頁 link/unlink（link 走整頁 POST→302 Google；unlink 無密碼時 422 驗證錯誤）
- [x] local 關閉 + 頁面說明 + 路由 404（ADR-007；Login/Register/Profile 顯示停用文案）
- [x] `php artisan test` 全綠（62 passed / 7 skipped）；`npm run build` 成功

### 執行摘要

安裝 `laravel/socialite ^5.27`，新增 migration 為 `users` 加 `google_id`（unique nullable）、`google_token`、`google_refresh_token`，並將 `password` 改為 nullable。`config/services.php` 新增 `google` 區塊與 ADR-007 的 `enabled` 計算。`GoogleAuthController` 提供 `redirect` / `callback` / `linkRedirect` / `unlink`：callback 依登入狀態分流——未登入走 ADR-004（已綁 → 登入；email 既存無 google_id → 不建帳；否則建新帳），已登入則綁定至當前帳號。`UnlinkGoogleAccountRequest` 於解除綁定前確認帳號仍保有密碼。Inertia 透過 `HandleInertiaRequests::share` 傳 `googleOAuth`（enabled / disabledReason）與 `googleAccount`（linked / hasPassword）。前端新增共用元件 `GoogleAuthButton.vue`（Login/Register 啟用顯示按鈕、未啟用顯示停用說明），及 `Profile/Partials/GoogleAccountForm.vue`（綁定狀態、link/unlink、未啟用說明），並掛入 `Profile/Show.vue`。新增 `tests/Feature/GoogleAuthTest.php` 共 12 例，涵蓋啟用/停用、ADR-004、link/unlink、Inertia prop 與 ADR-007 local 強制關閉規則。

### Files Changed

新增：
- `app/Http/Controllers/GoogleAuthController.php`
- `app/Http/Requests/UnlinkGoogleAccountRequest.php`
- `database/migrations/2026_06_01_152744_add_google_columns_to_users_table.php`
- `resources/js/Components/GoogleAuthButton.vue`
- `resources/js/Pages/Profile/Partials/GoogleAccountForm.vue`
- `tests/Feature/GoogleAuthTest.php`
- `.env.example`（**注意：本 repo `.gitignore` 忽略 `.env.example`，故不會進 git diff**；見 Deviations）

修改：
- `.env.build`（新增 Google OAuth 區塊，tracked 部署模板）
- `app/Http/Middleware/HandleInertiaRequests.php`（share `googleOAuth` / `googleAccount`）
- `app/Models/User.php`（`fillable` 加 `google_id`；`hidden` 加 token 欄位）
- `composer.json` / `composer.lock`（laravel/socialite）
- `config/services.php`（`google` 區塊 + `enabled` 計算）
- `resources/js/Pages/Auth/Login.vue`、`Auth/Register.vue`（嵌入 GoogleAuthButton）
- `resources/js/Pages/Profile/Show.vue`（引入 GoogleAccountForm）
- `routes/web.php`（4 條 Google 路由）

> 註：`.cursor/mcp.json` 於任務開始前即為 modified，與 Phase 2 無關，未觸碰。

### Verification

```
# php artisan test tests/Feature/GoogleAuthTest.php
Tests: 12 passed (47 assertions)

# php artisan test（全套）
Tests: 7 skipped, 62 passed (197 assertions)

# vendor/bin/pint --dirty
{"tool":"pint","result":"fixed",...}  → routes/web.php import 排序；再次執行為 clean

# npm run build
✓ built（僅 chunk >500kB 之資訊性警告）

# php artisan route:list | grep google
GET  auth/google/redirect / callback；POST user/google/link；DELETE user/google/unlink
```

### Deviations

- **`config/services.php` 的 `enabled` 用 `env('APP_ENV') !== 'local'` 取代 handoff 範例的 `app()->environment('local')`。** 原因：config 於 `LoadConfiguration` 階段載入時 `$app['env']` 尚未綁定，呼叫 `app()->environment()` 會丟出 `Target class [env] does not exist`。行為與 ADR-007 完全一致（local 強制關閉），並以測試 `forces oauth off on local environment` 直接驗證。
- **`.env.example` 在本 repo 被 `.gitignore` 忽略**（無 tracked `.env.example`，原 tracked 模板為 `.env.build`）。為符合 handoff 指示仍建立了 `.env.example`（含 Google 區塊、`GOOGLE_OAUTH_ENABLED=false`），但它不會出現在 `git diff`；可被 git diff 驗證的 Google env 設定改放於 tracked 的 `.env.build`。若需納管 `.env.example`，請人類調整 `.gitignore`。
- **`docs/deployment.md` 的「Google OAuth」章節已於 Phase 1 review 後補先行存在**（含 Console redirect URI、佈署 env、本機說明），內容已足夠 ADR-007 需求，故 Phase 2 未重複新增。
- **link 綁定採整頁 `<form method=POST>`（非 Inertia）**，因 controller 回 302 導向外部 Google，Inertia XHR 無法跟隨跨網域 redirect；CSRF 以 blade `meta[name=csrf-token]` 帶入 `_token`。unlink 為內部 redirect，沿用 Inertia `useForm().delete()`。

### Issues / 風險留待 Phase 3

- 端到端真實 Google OAuth：**不由 Phase 2 review 阻擋**；由人類於上佇署後依 `docs/deployment.md` § 佈署後 Google 驗證清單手動確認（2026-06-01 已記錄）。
- callback 成功後導向 `route('dashboard')`；`dashboard` 路由含 `verified` middleware，目前 `User` 未實作 `MustVerifyEmail`，Google 新帳號可正常進入；若 Phase 3+ 啟用 email 驗證需重新評估此導向。

### Review 檢查點

- [x] 無私人房 / chat_room_members 等 Phase 3 檔案
- [x] local 強制關閉（ADR-007；config 計算 + 測試 + 三頁面說明）
- [x] GoogleAuthTest 存在且通過（12 例）
- [x] .env.example 與 deployment.md 已更新（見 Deviations：.env.example 受 gitignore、deployment.md 既有章節已足夠）

---

## Completed

- 2026-06-01：交付 `.ai-dev/private-room/` 四份規格檔（plan / progress / decisions / handoff）
- 2026-06-01：唯讀環境盤點寫入 `decisions.md`（Laravel 12.35.1, PHP 8.4.21）
- 2026-06-01：規格修訂 ADR-007（Google 僅佈署環境、本機頁面說明）
- 2026-06-01：新增 `phase-1-handoff.md`（Phase 2+ handoff 待 Phase 1 驗收後再寫）
- 2026-06-01：Phase 1 — Laravel 13 升級完成（執行者：Claude）
- 2026-06-01：Phase 1 Review PASS；文件 Laravel 13 對齊；新增 `phase-2-handoff.md`
- 2026-06-01：Phase 2 — Google OAuth（Socialite）註冊/登入/設定頁綁定完成（執行者：Claude）
- 2026-06-01：Phase 2 Review PASS；Google 佈署 E2E 改由人類上線後驗證（見 deployment.md）
- 2026-06-01：新增 `phase-3-handoff.md`

---

## Deviations

| 日期 | 摘要 |
|------|------|
| 2026-06-01 | 規格修訂：ADR-007 Google OAuth 本機強制關閉、佈署環境啟用；更新 plan / handoff（實作未開始） |
| 2026-06-01 | Phase 1：為解析 L13 連帶升級 inertia v3 / tinker v3 / pest v4 / openai-php v0.19 / boost v2（相依性必需）；`docs/deployment.md` 無版本敘述故未改 |
| 2026-06-01 | Review 後補：根 README、`docs/*` Laravel 13；`deployment.md` 新增 Google OAuth 佈署說明 |
| 2026-06-01 | Phase 2：config `enabled` 改用 `env('APP_ENV')`（避開 config 載入期 `app()->environment()` 失敗）；`.env.example` 受 gitignore 故 Google env 設定亦放 tracked `.env.build`；link 綁定採整頁 POST（非 Inertia，跨網域 redirect）。詳見 Phase 2 執行回報 |

---

## Issues

None

---

## Files Changed（階段 A）

- `.ai-dev/private-room/plan.md`（新增/完整內容）
- `.ai-dev/private-room/progress.md`（本檔）
- `.ai-dev/private-room/decisions.md`（新增）
- `.ai-dev/private-room/handoff.md`（新增）

---

## Verification（階段 A）

- 階段 A 僅交付 `.ai-dev/private-room/` 規格檔，未改應用程式
- 規格含 Phase 1–5 Success Criteria 與 STOP 規則

---

## Next Steps

1. 人類上佇署後依 [`docs/deployment.md`](../../docs/deployment.md) § 佈署後 Google 驗證（非 Phase 3 前置條件）
2. 將 [`phase-3-handoff.md`](phase-3-handoff.md) 交給執行 agent（私人房後端）
3. **勿**在未指派時開始 Phase 4（前端）

---

## 階段 C — Review

| 項目 | 狀態 | 日期 |
|------|------|------|
| Phase 1 Review | ✅ PASS | 2026-06-01 |
| Phase 2 Review | ✅ PASS | 2026-06-01 |
| 全專案 Review | ⏳ 未開始 | Phase 5 完成後 |

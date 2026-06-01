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
| Phase 3 私人房後端 | ✅ 完成 | 2026-06-01 | 見下方 Phase 3 執行回報 |
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

## Phase 3 執行回報

**執行者：** Claude (Claude Code)
**完成日期：** 2026-06-01
**狀態：** ✅ 完成

### Success Criteria

- [x] 成員 / 邀請 / accept 流程測試通過
- [x] 非成員 403（檢視私人房、發訊到私人房皆 403）
- [x] 私人房廣播 `PrivateChannel`；主題房仍 `Channel`（ADR-003）
- [x] `php artisan test` 全綠（77 passed / 7 skipped）
- [x] 無 Phase 4 專屬 UI 檔案（僅最小 Inertia props：roomMode / canClear / privateRooms / members）

### 執行摘要

新增三個 migration：`chat_rooms` 加 `type`（預設 `global_theme`）與 `created_by`；新表 `chat_room_members`（含 unique(chat_room_id,user_id)）與 `chat_room_invitations`（token unique、expires_at、revoked_at、accepted_*、max_uses）。新增 `ChatRoomMember`、`ChatRoomInvitation` model，並擴充 `ChatRoom`（常數 TYPE_*、MAX_MEMBERS=20、`members()`/`memberRecords()`/`invitations()`/`creator()` 關聯、`isPrivateGroup()`/`hasMember()`/`isOwnedBy()`/`broadcastChannel()`）。新增 `ChatRoomPolicy`（view/sendMessage/clear/invite/removeMember/delete，L11+ 自動發現）。新增 `PrivateChatRoomController`（index/show/store/storeInvitation/acceptInvitation/destroyMember）與 `StorePrivateChatRoomRequest`，路由註冊於 `/chat/{theme}` 之前避免 `private` 被當主題解析。`ChatRoomController` 抽出 `resolveRoomForAction()`：帶 `room` 參數時解析私人房並套用 Policy（view/sendMessage/clear），否則沿用既有主題 slug 解析；`canClearChatRoom()` 加上 private_group→owner 分支。三個廣播 Event 改用 `ChatRoom::broadcastChannel()`（私人房 PrivateChannel、主題房 Channel），`routes/channels.php` 改為主題房任何登入者可訂閱、私人房僅成員。新增 `ChatRoomFactory` 與 `tests/Feature/PrivateChatRoomTest.php`（15 例）。

### Files Changed

新增：
- `app/Http/Controllers/PrivateChatRoomController.php`
- `app/Http/Requests/StorePrivateChatRoomRequest.php`
- `app/Models/ChatRoomMember.php`、`app/Models/ChatRoomInvitation.php`
- `app/Policies/ChatRoomPolicy.php`
- `database/factories/ChatRoomFactory.php`
- `database/migrations/2026_06_01_154824_add_type_and_created_by_to_chat_rooms_table.php`
- `database/migrations/2026_06_01_154824_create_chat_room_members_table.php`
- `database/migrations/2026_06_01_154824_create_chat_room_invitations_table.php`
- `tests/Feature/PrivateChatRoomTest.php`

修改：
- `app/Models/ChatRoom.php`（type/members/helpers/broadcastChannel + HasFactory）
- `app/Http/Controllers/ChatRoomController.php`（resolveRoomForAction + Policy + canClear 私人房分支 + index 最小 props）
- `app/Events/ChatMessageCreated.php`、`AiReplyCompleted.php`、`ChatRoomCleared.php`（broadcastChannel 分支）
- `routes/channels.php`（成員檢查）
- `routes/web.php`（6 條私人房路由，置於 `/chat/{theme}` 之前）

> 註：未變更任何 `.vue`；`.cursor/mcp.json` 與本 Phase 無關，未觸碰。

### Verification

```
# php artisan migrate
3 個新 migration DONE（add_type_and_created_by / create_chat_room_invitations / create_chat_room_members）

# php artisan test tests/Feature/PrivateChatRoomTest.php
Tests: 15 passed (30 assertions)

# php artisan test（全套）
Tests: 7 skipped, 77 passed (227 assertions)  — 既有 ChatRoom/主題房測試全數沿用通過

# vendor/bin/pint --dirty
{"tool":"pint","result":"fixed"}  → import/格式微調；再次執行為 clean
```

### Deviations

- **`isGlobalTheme()` 維持 slug-based（未改為 `type === global_theme`）。** 原因：既有測試以 `user_id` 擁有的非主題房（slug 如 `private-lab`）測 clear 流程；若改成 type-based 會把這些（預設 type=global_theme）誤判為主題房而破壞回歸測試。改以新增 `isPrivateGroup()`（type-based）區分私人房，三房型行為皆正確且回歸測試全綠。
- **私人房訊息沿用既有 `/chat/send-message[-stream]`、`/chat/load-more`、`/chat/clear`，以 `room` 參數識別**（非新增私人房專屬訊息端點），符合 handoff「沿用 ChatRoomController，抽出 resolve 分岐、不改 SSE」。帶 `room` 但非私人房 id 一律 404，避免越權至任意房。
- **`max_uses` 欄位已建但暫未強制**（第一版以房成員上限 20 與 expires_at/revoked_at 控管；無對應測試）。留待後續如需多用次數限制再啟用。
- **最小 Inertia props**：`ChatRoomController::index` 增加 `roomMode`/`canClear`；`PrivateChatRoomController` index/show 回傳 `privateRooms`/`members`/`roomMode`/`canClear`，供 Phase 4 接線；**未改任何 `.vue`**。

### Issues / 風險留待 Phase 4

- 前端尚未接線：`Echo.private()` 訂閱、ChatSidebar 雙區導覽、建立房/邀請 modal、私人房訊息送出帶 `room` 參數，皆屬 Phase 4。後端 channel 授權已就緒。
- `PrivateChatRoomController` index/show 目前 render `ChatRoom` 元件但前端尚未處理 `roomMode='private'`/`privateRooms`/`members`；Phase 4 需在 `ChatRoom.vue` 接收這些 props。
- 邀請連結為多次可用（至 expire/revoke/滿員）；`accepted_at`/`accepted_by` 僅記錄最後一次接受（資訊性）。

### Review 檢查點

- [x] 無 Phase 4 UI（未動 ChatSidebar、Welcome、Dashboard、ChatRoom.vue）
- [x] 主題房行為未破壞（既有 ChatRoomControllerTest 全綠，含 owned-room clear）
- [x] PrivateChannel + channels.php 成員檢查（測試覆蓋 broadcastOn 與 channel 授權邏輯）
- [x] 邀請制符合 ADR-002/005（owner 建房、邀請、accept、20 人上限、7 天 expire、可 revoke）

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
- 2026-06-01：Phase 3 — 邀請制私人房後端（migration / Model / Policy / Controller / PrivateChannel / 測試）完成（執行者：Claude）
- 2026-06-01：Phase 3 Review PASS；程式 commit 推送
- 2026-06-01：新增 `phase-4-handoff.md`

---

## Deviations

| 日期 | 摘要 |
|------|------|
| 2026-06-01 | 規格修訂：ADR-007 Google OAuth 本機強制關閉、佈署環境啟用；更新 plan / handoff（實作未開始） |
| 2026-06-01 | Phase 1：為解析 L13 連帶升級 inertia v3 / tinker v3 / pest v4 / openai-php v0.19 / boost v2（相依性必需）；`docs/deployment.md` 無版本敘述故未改 |
| 2026-06-01 | Review 後補：根 README、`docs/*` Laravel 13；`deployment.md` 新增 Google OAuth 佈署說明 |
| 2026-06-01 | Phase 2：config `enabled` 改用 `env('APP_ENV')`（避開 config 載入期 `app()->environment()` 失敗）；`.env.example` 受 gitignore 故 Google env 設定亦放 tracked `.env.build`；link 綁定採整頁 POST（非 Inertia，跨網域 redirect）。詳見 Phase 2 執行回報 |
| 2026-06-01 | Phase 3：`isGlobalTheme()` 維持 slug-based（避免破壞既有 owned-room 回歸測試）；私人房訊息沿用既有端點以 `room` 參數識別；`max_uses` 已建欄位但暫不強制。詳見 Phase 3 執行回報 |

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

1. 將 [`phase-4-handoff.md`](phase-4-handoff.md) 交給執行 agent（前端 UI + Echo.private）
2. 人類上佇署後依 [`docs/deployment.md`](../../docs/deployment.md) § 佈署後 Google 驗證（可與 Phase 4 並行）
3. **勿**在未指派時開始 Phase 5

---

## 階段 C — Review

| 項目 | 狀態 | 日期 |
|------|------|------|
| Phase 1 Review | ✅ PASS | 2026-06-01 |
| Phase 2 Review | ✅ PASS | 2026-06-01 |
| Phase 3 Review | ✅ PASS | 2026-06-01 |
| 全專案 Review | ⏳ 未開始 | Phase 5 完成後 |

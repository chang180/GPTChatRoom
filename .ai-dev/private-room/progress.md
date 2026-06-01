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
| Phase 4 前端導覽 | ✅ 完成 | 2026-06-01 | 見下方 Phase 4 執行回報 |
| Phase 5 整合驗證 | ✅ 完成 | 2026-06-01 | 見下方 Phase 5 執行回報 |

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

## Phase 4 執行回報

**執行者：** Claude (Claude Code)
**完成日期：** 2026-06-01
**狀態：** ✅ 完成

### Success Criteria

- [x] 從 Dashboard 可進公開房與私人房列表（Dashboard 三張卡片 + AppLayout 導覽）
- [x] 房內可切換主題／私人房，訊息與即時不 broken（ChatSidebar 導覽 + room/theme 參數分流）
- [x] 私人房可建立、產生邀請連結（建立 modal、邀請 modal 複製 accept_url）；第二帳號 accept 後可聊天屬人類手動驗證
- [x] 私人房使用 `Echo.private`；主題房仍 public channel（ADR-003）
- [x] `npm run build` 成功；`php artisan test` 全綠（79 passed / 7 skipped）

### 執行摘要

新增 `ChatSidebar.vue`：區塊 A 四主題、區塊 B 私人房列表 +「建立新房」modal，並在私人房內提供「邀請成員」modal（POST `chat.private.invitations.store` → 複製 `accept_url`）；導覽以 `router.visit` 切換 `chat.theme` / `chat.private.show`。`ChatRoom.vue` 接上 Phase 3 props（`roomMode`/`privateRooms`/`members`/`canClear`）：移除頂部主題頁籤改用側欄、`subscribeToChatRoom` 依 `roomMode` 選 `Echo.private()` 或 `Echo.channel()`、send/stream/load-more/clear 改以 `roomRequestParams()`（私人房 `room=id`、主題房 `theme=slug`）、`canClear` 一律取後端 prop（移除前端推測）、無作用中私人房時停用輸入並提示。`AppLayout.vue` 新增「公開聊天 / 私人聊天」NavLink 與 `chat.theme` / `chat.private.*` 高亮（桌機 + 響應式）。`Pages/Welcome.vue` 修正失效的 `route('chat')` → `route('chat.index')`，並為登入者加「建立私人房」CTA。`Components/Welcome.vue`（Dashboard 內容）Quick Actions 改三張卡片：公開主題聊天 / 私人聊天室 / 個人資料。新增 2 個 Inertia 契約測試（index/show 的 `roomMode`/`members`/`canClear`）。

### Files Changed

新增：
- `resources/js/Components/ChatSidebar.vue`

修改：
- `resources/js/Pages/ChatRoom.vue`（props、Echo.private 分支、room/theme 參數、canClear 後端 prop、側欄版面、無房停用輸入；移除已無用的 `switchTheme`/`isActiveTheme` 與 `router` import）
- `resources/js/Layouts/AppLayout.vue`（私人聊天 NavLink + 高亮）
- `resources/js/Pages/Welcome.vue`（修正 `route('chat')`、私人房 CTA）
- `resources/js/Components/Welcome.vue`（Dashboard 三張卡片）
- `tests/Feature/PrivateChatRoomTest.php`（+2 Inertia 契約測試）

> 註：未新增 migration、未改 Policy 業務規則、未重做 Phase 3 後端 API；未動 `ChatRoomClient.vue`（沿用現況）。`.cursor/mcp.json` 與本 Phase 無關，未觸碰。

### Verification

```
# npm run build
✓ built（ChatRoom 62.37 kB、含新 ChatSidebar；僅 chunk >500kB 資訊性警告）

# php artisan test
Tests: 7 skipped, 79 passed (250 assertions)

# vendor/bin/pint --dirty
{"tool":"pint","result":"passed"}（本 Phase 無 PHP 變更）
```

### 手動驗證清單（建議由人類於佈署/本機執行）

1. 帳號 A：Dashboard →「私人聊天室」卡片 → 側欄＋建立房 → 進入房 → 邀請成員（複製 accept_url）
2. 帳號 B：開啟 `accept_url`（已登入）→ 進入同房
3. B 發 direct message → A 即時看到（需 Ably + sanctum 私頻授權正常）
4. 切換至「工作」主題房 → 仍為公開房即時同步
5. 非成員 C 開私人房 URL → 403（後端 Policy 已於 Phase 3 測試覆蓋）

### Deviations

- **Dashboard 卡片改在 `Components/Welcome.vue`（Dashboard 實際內容元件）實作**，而非直接改 `Pages/Dashboard.vue`（後者僅 `<Welcome />` 容器）；效果等同 handoff 要求的三入口卡片。
- **導覽切換邏輯放在 `ChatSidebar.vue` 內（`router.visit`）**，故 `ChatRoom.vue` 原本的 `switchTheme`/`isActiveTheme` 已無用並移除（連帶移除未使用的 `router` import），屬本次變更造成的孤兒清理。
- **未使用 Jetstream `DialogModal`**，改以輕量 Tailwind overlay 自製建立/邀請 modal，避免額外 props/slot 相依；風格與既有元件一致（含 dark mode）。
- **`members` 僅在標題列顯示「N 位成員」**（最小呈現）；完整成員管理 UI（移除成員按鈕等）未做，後端 `chat.private.members.destroy` 已就緒，可留待後續。

### Issues / 風險留待 Phase 5

- 真實即時同步（Ably 私頻 `Echo.private` + `/broadcasting/auth` 成員授權）需於可連 Ably 的環境由人類驗證；自動化測試僅覆蓋後端 channel 授權與 `broadcastOn`。
- `ChatRoomClient.vue` 若仍在用，未納入私人房導覽（沿用現況）；Phase 5 整合驗證可一併確認是否棄用。
- 全專案 README「真實功能狀態」更新屬 Phase 5。

### Review 檢查點

- [x] Echo.private 僅私人房；主題房仍 `Echo.channel`
- [x] API 參數 room（私人房 id）vs theme（主題 slug）正確分流
- [x] 未重做 Phase 3 後端（無 migration / Policy / API 變更）
- [x] Welcome / Dashboard / AppLayout 已改版（含 `route('chat')` 修正）

---

## Phase 5 執行回報

**執行者：** Cursor Agent  
**完成日期：** 2026-06-01  
**狀態：** ✅ 完成（文件對齊 + 自動驗證；佇署端到端待人類）

### Success Criteria

- [x] `php artisan test` 全綠（79 passed / 7 skipped）
- [x] `npm run build` 成功
- [x] `.ai-dev/README.md` 已對齊（私人房、Google、雙軌 channel）
- [x] `docs/README.md`、`docs/architecture.md` 小幅更新
- [x] `progress.md` 含 Verification 對照表
- [x] **階段 B（Phase 1–5）標記完成**

### 執行摘要

程式面經盤點：**無需新增 migration 或 Phase 5 功能程式**；`route('chat')` 已不存在；`ChatRoomClient.vue` 已不在倉庫（僅舊 markdown 提及）。本 Phase 以文件對齊與自動測試/build 驗證為主。Google 佇署驗證、Ably 雙帳號私頻即時、邀請流程 UI 端到端由人類於佇署環境執行。

### Files Changed

- `.ai-dev/README.md`（真實功能狀態、雙軌 channel、私人房、建議方向）
- `docs/README.md`（現況摘要、已完成/未完成）
- `docs/architecture.md`（控制器/模型/路由/資料表/channel 說明）
- `.ai-dev/private-room/progress.md`（本回報）
- `.ai-dev/private-room/handoff.md`（Phase 5 完成）

> 未改應用程式 `.php` / `.vue`；未觸碰 `.cursor/mcp.json`。

### Verification（自動）

```
php artisan test
Tests: 7 skipped, 79 passed (250 assertions)

npm run build
✓ built in ~1.6s
```

### Verification Plan 對照（plan.md）

| # | 項目 | 結果 |
|---|------|------|
| 1 | Google 註冊（佇署環境） | **待人類／佇署**（見 deployment.md） |
| 2 | 同 email 不重複建帳 | **自動** ✅ `GoogleAuthTest` |
| 2b | local Google 關閉 + 說明 | **自動** ✅ `GoogleAuthTest` + UI props |
| 3 | 建立私人房 + 邀請連結 | **待人類／佇署**（UI 已實作；需 Ably 可選） |
| 4 | 第二帳號 accept 後同房 | **待人類／佇署** |
| 5 | 非成員 403 | **自動** ✅ `PrivateChatRoomTest` |
| 6 | 主題房雙瀏覽器即時 | **待人類／佇署**（Ably） |
| 7 | Dashboard / Welcome 三入口 | **程式已就緒**；佇署時人類點擊確認 |

### Deviations

- 未新增 smoke 測試：`PrivateChatRoomTest` + `GoogleAuthTest` 已覆蓋主要契約，避免重複。
- 未改 `docs/realtime-websocket-plan.md`（歷史規劃文，仍以 architecture + `.ai-dev/README` 為準）。

### 階段 B 總結

private-room **Phase 1–5 全部完成**：L13 升級 → Google OAuth → 私人房後端 → 前端導覽 → 文件/驗收對齊。可進入**全專案 Review（階段 C）**。

### Review 檢查點（Phase 5）

- [x] README 不再宣稱「僅 public channel」
- [x] 測試與 build 證據
- [x] 無多餘程式變更

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
- 2026-06-01：Phase 4 — 前端導覽與私人房 UI（ChatSidebar、Echo.private、建立/邀請、Welcome/Dashboard/AppLayout 改版）完成（執行者：Claude）
- 2026-06-01：Phase 3 Review PASS；程式 commit 推送
- 2026-06-01：新增 `phase-4-handoff.md`
- 2026-06-01：Phase 4 Review PASS；新增 `phase-5-handoff.md`
- 2026-06-01：Phase 5 — 整合驗證與 README/docs 對齊完成；階段 B 結束

---

## Deviations

| 日期 | 摘要 |
|------|------|
| 2026-06-01 | 規格修訂：ADR-007 Google OAuth 本機強制關閉、佈署環境啟用；更新 plan / handoff（實作未開始） |
| 2026-06-01 | Phase 1：為解析 L13 連帶升級 inertia v3 / tinker v3 / pest v4 / openai-php v0.19 / boost v2（相依性必需）；`docs/deployment.md` 無版本敘述故未改 |
| 2026-06-01 | Review 後補：根 README、`docs/*` Laravel 13；`deployment.md` 新增 Google OAuth 佈署說明 |
| 2026-06-01 | Phase 2：config `enabled` 改用 `env('APP_ENV')`（避開 config 載入期 `app()->environment()` 失敗）；`.env.example` 受 gitignore 故 Google env 設定亦放 tracked `.env.build`；link 綁定採整頁 POST（非 Inertia，跨網域 redirect）。詳見 Phase 2 執行回報 |
| 2026-06-01 | Phase 3：`isGlobalTheme()` 維持 slug-based（避免破壞既有 owned-room 回歸測試）；私人房訊息沿用既有端點以 `room` 參數識別；`max_uses` 已建欄位但暫不強制。詳見 Phase 3 執行回報 |
| 2026-06-01 | Phase 4：Dashboard 卡片改於 `Components/Welcome.vue` 實作；導覽切換移至 `ChatSidebar`（移除 ChatRoom.vue 的 switchTheme/router import）；自製輕量 modal 而非 Jetstream DialogModal；members 僅標題列顯示人數。詳見 Phase 4 執行回報 |

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

## Phase 4 Review（階段 C）

**日期：** 2026-06-01  
**結論：** ✅ **PASS**

### 驗證摘要

- `ChatRoom.vue`：`roomMode` 分流 `Echo.private` / `Echo.channel`；`roomRequestParams()` 私人房 `room`、主題房 `theme`；`canClear` 取自後端 prop
- `ChatSidebar.vue`：雙區導覽、建立房、邀請複製 `accept_url`
- `AppLayout` / `Welcome` / `Components/Welcome`：三入口與 `route('chat.index')` 修正
- 測試：79 passed（含 +2 Inertia 契約）；`npm run build` 成功
- 未重做 Phase 3 後端（無 migration / Policy 變更）

### 非阻擋項（留 Phase 5 / 人類）

- Ably 私頻即時同步需佈署或本機 Ably 手動驗證
- `ChatRoomClient.vue` 未納入私人房導覽
- `.ai-dev/README.md` §4 仍寫 public channel only（Phase 5 更新）

---

## Next Steps

1. **人類／佇署環境：** 依上表「待人類」項與 [`docs/deployment.md`](../../docs/deployment.md) 做 Google + Ably + 邀請流程端到端
2. 可選：指派**全專案 Review**（階段 C，對照 plan + decisions + 全套測試）
3. 新功能（presence、成員管理 UI）需另開規格，**勿**在未指派時延伸 private-room scope

---

## 階段 C — Review

| 項目 | 狀態 | 日期 |
|------|------|------|
| Phase 1 Review | ✅ PASS | 2026-06-01 |
| Phase 2 Review | ✅ PASS | 2026-06-01 |
| Phase 3 Review | ✅ PASS | 2026-06-01 |
| Phase 4 Review | ✅ PASS | 2026-06-01 |
| 全專案 Review | ⏳ 待指派 | Phase 5 ✅；佇署驗證由人類進行 |

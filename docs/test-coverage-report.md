# GPTChatRoom 測試覆蓋率報告

| 項目 | 內容 |
|------|------|
| **專案** | GPTChatRoom（Laravel 13 + Inertia v3 即時聊天室） |
| **報告日期** | 2026-08-28 |
| **測量基準 commit** | `1778094` |
| **目標門檻** | 70%（主管口頭指示，未指定度量類型） |
| **報告版本** | 1.0 |

---

## 1. 執行摘要

本專案以 **Pest 4 / PHPUnit 12** 執行自動化測試，並以 **Xdebug** 收集 **`app/` 目錄**的程式碼覆蓋率。

| 指標 | 結果 | 70% 門檻 |
|------|------|----------|
| **行覆蓋率（Statements）** | **73.1%**（739 / 1,011 行） | ✅ 達標 |
| 方法覆蓋率（Methods） | 62.5%（95 / 152） | ⚠️ 未達 |
| 元素覆蓋率（Elements） | 71.7%（834 / 1,163） | ✅ 達標 |
| 測試執行 | **108 passed**、7 skipped、0 failed | — |
| 斷言數 | 345 | — |
| 執行時間 | 約 5.4 秒 | — |

**結論：** 若以業界最常引用的 **行覆蓋率** 衡量，本專案 **73.1%**，高於 70% 要求。核心業務流程（公開／私人聊天室、Admin 後台、Google OAuth、對話摘要）已有 Feature 測試支撐；主要缺口集中在 **OpenAI 串流呼叫**、**Artisan 維運指令** 與 **ChatRoomController 部分分支**。

> **給主管的說明：** 「70% 覆蓋率」若未明定計算範圍，本報告採 **後端應用程式碼（`app/`）** 為準，不含前端 Vue、路由檔、設定檔。若需改為「全 repo」或納入 CI 門檻，請另定規範（見 §6 建議）。

---

## 2. 測量方法

### 2.1 工具鏈

| 項目 | 版本／工具 |
|------|------------|
| PHP | 8.4.23（Laravel Herd，含 Xdebug） |
| 框架 | Laravel 13 |
| 測試框架 | Pest 4 |
| 覆蓋率驅動 | Xdebug |
| 設定檔 | `phpunit.xml` |

### 2.2 覆蓋範圍

```xml
<!-- phpunit.xml -->
<source>
    <include>
        <directory>app</directory>
    </include>
</source>
```

- **納入：** `app/` 下 46 個 PHP 檔（約 3,047 行原始碼）
- **未納入：** `resources/js/`（Vue）、`routes/`、`config/`、`database/`、Blade 範本
- **測試環境：** SQLite in-memory、`BROADCAST_CONNECTION=log`、OpenAI 以 mock 取代

### 2.3 重現指令

```bash
# 需 PHP 8.4 + Xdebug（Herd 預設 php 8.5 無 Xdebug，請用 php84）
"/Users/changchien-wen/Library/Application Support/Herd/bin/php84" artisan test --coverage

# 產生可點擊的 HTML 明細（本機瀏覽器開啟）
"/Users/changchien-wen/Library/Application Support/Herd/bin/php84" artisan test \
  --coverage-html=storage/coverage \
  --coverage-clover=storage/coverage/clover.xml
```

HTML 報告路徑：`storage/coverage/index.html`（逐檔、逐行標色，適合與主管一起點開看）

---

## 3. 模組別覆蓋率

### 3.1 依目錄彙整

| 目錄 | 行覆蓋率 | 已覆蓋 / 可測行 | 檔案數 | 備註 |
|------|----------|-----------------|--------|------|
| `Events/` | **100.0%** | 37 / 37 | 4 | 廣播事件全覆蓋 |
| `Http/Controllers/Admin/` | **100.0%** | 17 / 17 | 1 | 管理員後台 |
| `Actions/Jetstream/` | **100.0%** | 8 / 8 | 1 | 帳號刪除 |
| `Http/Requests/` | **94.4%** | 17 / 18 | 3 | Form Request 驗證 |
| `Providers/` | **93.9%** | 62 / 66 | 4 | Service Provider |
| `Actions/` | **91.7%** | 22 / 24 | 1 | 邀請接受流程 |
| `Support/` | **90.5%** | 19 / 21 | 1 | Pending invitation cookie |
| `Actions/Fortify/` | **83.7%** | 41 / 49 | 5 | 註冊／密碼 |
| `Policies/` | **83.3%** | 5 / 6 | 1 | 聊天室授權 |
| `Jobs/` | **80.0%** | 4 / 5 | 1 | 對話摘要 Job |
| `Http/Controllers/` | **73.4%** | 260 / 354 | 4 | 含 ChatRoomController |
| `Http/Middleware/` | **73.3%** | 22 / 30 | 3 | 1 個 middleware 未測 |
| `Models/` | **72.3%** | 60 / 83 | 6 | Eloquent 模型 |
| `Http/Responses/` | **66.7%** | 2 / 3 | 3 | 2FA 回應未測 |
| **`Services/`** | **64.2%** | 163 / 254 | 4 | **GPTService 拉低平均** |
| `Console/Commands/` | **0.0%** | 0 / 34 | 2 | 維運指令未測 |
| `View/Components/` | **0.0%** | 0 / 2 | 1 | UI 元件未測 |

### 3.2 覆蓋率最低檔案（優先改善）

| 檔案 | 行覆蓋率 | 未覆蓋行數 | 說明 |
|------|----------|------------|------|
| `Services/GPTService.php` | 2.8% | 69 | 測試以 fake 取代 OpenAI client，串流／錯誤處理本體未執行 |
| `Http/Controllers/ChatRoomController.php` | 57.0% | 83 | SSE 串流 AI 回覆、部分 edge case |
| `Console/Commands/PreloadMessageCache.php` | 0.0% | 18 | Artisan 快取預載 |
| `Services/MessageCacheService.php` | 60.9% | 18 | Redis 快取邏輯部分分支 |
| `Console/Commands/FixUserPasswords.php` | 0.0% | 16 | 一次性維運指令 |
| `Models/ChatRoom.php` | 72.2% | 15 | 部分 scope／helper |
| `Http/Middleware/HandleAuthenticationErrors.php` | 0.0% | 8 | 認證錯誤 middleware |
| `Actions/Fortify/UpdateUserProfileInformation.php` | 60.0% | 8 | 頭像上傳等分支 |

---

## 4. 測試套件概況

### 4.1 測試檔案分布（26 個測試檔）

| 類別 | 檔案數 | 代表範圍 |
|------|--------|----------|
| **業務 Feature** | 12 | 聊天室、私人房、Admin、Google OAuth、首頁 |
| **Jetstream / Fortify** | 13 | 登入、註冊、2FA、密碼重設、API Token（部分 skip） |
| **Unit** | 3 | Message 模型、廣播設定、ConversationContextService |

### 4.2 業務功能測試對照

| 功能域 | 測試檔 | 狀態 |
|--------|--------|------|
| 公開主題聊天 | `ChatRoomControllerTest` | ✅ 13 cases |
| 私人聊天室 | `PrivateChatRoomTest` | ✅ 25 cases |
| 管理員後台 | `Admin/*` | ✅ 12 cases |
| Google 登入／綁定 | `GoogleAuthTest` | ✅ 13 cases |
| 對話摘要／上下文 | `ConversationContextServiceTest` | ✅ 12 unit cases |
| 認證錯誤處理 | `AuthenticationErrorHandlingTest` | ✅ 5 cases（未涵蓋 middleware 本體） |

### 4.3 Skipped 測試（7 個）

多為 Jetstream 範本測試，因專案未啟用 API Token 或關閉註冊而 skip，**不影響覆蓋率計算**，但建議在報告中向主管說明為「功能未啟用，刻意略過」。

---

## 5. 風險與品質評估

### 5.1 已充分覆蓋（低風險）

- 使用者註冊、登入、密碼變更（Fortify / Jetstream）
- 公開主題切換、發訊、清除（含 Admin 權限）
- 私人房建立、邀請、成員、廣播 channel 類型
- 首位使用者自動成為 Admin、最後一位 Admin 保護
- 對話上下文與摘要 checkpoint（`ConversationContextService`）

### 5.2 覆蓋不足但可接受（中風險）

| 項目 | 理由 |
|------|------|
| `GPTService` 2.8% | 外部 API 依賴；Feature 測試已 mock 回應，**整合行為有測、實作細節未測** |
| Artisan Commands 0% | 維運／一次性工具，非使用者-facing |
| `DarkModeToggle` 0% | 純 UI Blade 元件 |

### 5.3 建議補強（若需拉高至 80%+）

1. **`ChatRoomController` 串流分支** — 以 fake HTTP stream 或 integration test 覆蓋 SSE 路徑（約 +8% 整體）
2. **`GPTService` unit test** — mock `OpenAI\Client`，測 timeout、stream chunk、錯誤轉換（約 +5%）
3. **`MessageCacheService`** — 以 `Cache::fake()` 或 Redis mock 補齊（約 +2%）
4. **`HandleAuthenticationErrors` middleware** — 2～3 個 Feature case（約 +1%）

---

## 6. 待主管確認的事項

主管指示「70% 覆蓋率」時未明定以下項目，建議開會時確認：

| # | 問題 | 本報告假設 |
|---|------|------------|
| 1 | 覆蓋率算 **行**、**分支** 還是 **方法**？ | **行（Statements）** |
| 2 | 範圍是否只算 `app/`？ | **是**，不含前端 |
| 3 | 是否要在 **CI/CD** 強制門檻？ | 尚未設定 |
| 4 | 未覆蓋的 **維運指令** 是否計入考核？ | 建議排除或另列 |
| 5 | **第三方 API**（OpenAI）mock 後的低覆蓋是否可接受？ | 建議接受，以 Feature 行為為準 |

---

## 7. 建議後續行動

| 優先 | 行動 | 預估效益 |
|------|------|----------|
| P0 | 將本報告與 `storage/coverage/index.html` 提供主管 review | 對齊「70%」定義 |
| P1 | CI 加入 `php artisan test --coverage --min=70`（需 runner 安裝 PCOV/Xdebug） | 可持續驗證 |
| P2 | 補 `ChatRoomController` 串流測試 | 整體 → ~80% |
| P3 | 補 `GPTService` unit tests | Services 目錄 → ~85% |
| P4 | 決定 Artisan Commands 是否納入覆蓋率考核 | 避免維運碼拉低數字 |

---

## 8. 附錄

### A. 完整檔案覆蓋率清單

<details>
<summary>點開查看 46 個 app/ 檔案</summary>

| 檔案 | 覆蓋率 |
|------|--------|
| Actions/AcceptChatRoomInvitation.php | 91.7% |
| Actions/Fortify/CreateNewUser.php | 100.0% |
| Actions/Fortify/PasswordValidationRules.php | 100.0% |
| Actions/Fortify/ResetUserPassword.php | 100.0% |
| Actions/Fortify/UpdateUserPassword.php | 100.0% |
| Actions/Fortify/UpdateUserProfileInformation.php | 60.0% |
| Actions/Jetstream/DeleteUser.php | 100.0% |
| Console/Commands/FixUserPasswords.php | 0.0% |
| Console/Commands/PreloadMessageCache.php | 0.0% |
| Events/AiReplyCompleted.php | 100.0% |
| Events/ChatMessageCreated.php | 100.0% |
| Events/ChatRoomCleared.php | 100.0% |
| Events/PrivateChatRoomClosed.php | 100.0% |
| Http/Controllers/Admin/UserManagementController.php | 100.0% |
| Http/Controllers/ChatRoomController.php | 57.0% |
| Http/Controllers/Controller.php | 100.0% |
| Http/Controllers/GoogleAuthController.php | 89.8% |
| Http/Controllers/HomeController.php | 100.0% |
| Http/Controllers/PrivateChatRoomController.php | 94.2% |
| Http/Middleware/EnsureUserIsAdmin.php | 100.0% |
| Http/Middleware/HandleAuthenticationErrors.php | 0.0% |
| Http/Middleware/HandleInertiaRequests.php | 100.0% |
| Http/Requests/StorePrivateChatRoomRequest.php | 100.0% |
| Http/Requests/UnlinkGoogleAccountRequest.php | 88.9% |
| Http/Requests/UpdateUserAdminRequest.php | 100.0% |
| Http/Responses/LoginResponse.php | 100.0% |
| Http/Responses/RegisterResponse.php | 100.0% |
| Http/Responses/TwoFactorLoginResponse.php | 0.0% |
| Jobs/SummarizeConversationJob.php | 80.0% |
| Models/ChatRoom.php | 72.2% |
| Models/ChatRoomInvitation.php | 60.0% |
| Models/ChatRoomMember.php | 0.0% |
| Models/ConversationSummary.php | 80.0% |
| Models/Message.php | 100.0% |
| Models/User.php | 80.0% |
| Policies/ChatRoomPolicy.php | 83.3% |
| Providers/AppServiceProvider.php | 78.6% |
| Providers/FortifyServiceProvider.php | 97.4% |
| Providers/GPTServiceProvider.php | 100.0% |
| Providers/JetstreamServiceProvider.php | 100.0% |
| Services/AdminGuard.php | 100.0% |
| Services/ConversationContextService.php | 97.0% |
| Services/GPTService.php | 2.8% |
| Services/MessageCacheService.php | 60.9% |
| Support/PendingChatRoomInvitation.php | 90.5% |
| View/Components/DarkModeToggle.php | 0.0% |

</details>

### B. 產物清單

| 檔案 | 用途 |
|------|------|
| `docs/test-coverage-report.md` | 本報告（可轉 PDF 或貼 Confluence） |
| `storage/coverage/index.html` | 互動式逐行覆蓋率（本機開啟） |
| `storage/coverage/clover.xml` | 機器可讀格式（SonarQube / GitLab CI 可用） |

---

*報告產生方式：2026-08-28 於本機執行 Pest 全量測試 + Xdebug coverage 收集。*

# 生產環境部署提醒

本文件給在生產環境以 `git pull` 後由人工或 agent 佈署時使用。每次佈署前請先確認目前 commit 是否包含資料庫 migration。

## 對話小結切點（Conversation Summary Checkpoint）

此功能在同房 `user` / `gpt` 訊息超過 20 則時，於 AI 請求前增量呼叫 OpenAI 產生小結，寫入 `conversation_summaries`，並以 DB 鎖（`summarizing_at` / `summarizing_until`）降低並發 summarize 競爭。小結僅供後端 AI 上下文，**不會**出現在聊天 UI。

### 必做步驟（pull 之後）

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

若佈署流程會快取設定，可在 migration 成功後執行：

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

本次變更**僅後端**，未修改前端資源；若生產環境慣例是每次 release 都 build，可照常執行 `npm ci && npm run build`，否則可略過。

### 環境需求

- `.env` 必須設定有效的 `OPENAI_API_KEY`（小結與聊天共用）
- 需能對 OpenAI 發出額外一次非串流 `chat.create`（觸發小結時）

## Google OAuth（Phase 2 起）

Google 登入／註冊**僅在已佈署環境**啟用；`APP_ENV=local` 時應用程式會強制關閉，無需在本機設定 Console redirect。

### 佈署時（staging / production）

1. [Google Cloud Console](https://console.cloud.google.com/) 建立 OAuth 用戶端（Web）。
2. **授權重新導向 URI** 須包含實際網域，例如：  
   `https://your-domain.example/auth/google/callback`  
   （與 `.env` 的 `GOOGLE_REDIRECT_URI` 完全一致。）
3. `.env` 設定：

```env
GOOGLE_OAUTH_ENABLED=true
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://your-domain.example/auth/google/callback
```

4. 佈署後執行 migration（含 Google 欄位）：

```bash
php artisan migrate --force
```

5. `php artisan config:cache`（若慣例有快取設定）。

### 佈署後 Google 驗證（由維運／人類在 staging / production 執行）

自動化測試（`GoogleAuthTest`）已在 CI／本機以 mock 覆蓋；**真實 Google 端到端不在本機驗收**，請在已佈署環境依下列清單手動確認一次：

- [ ] 登入頁出現「使用 Google 繼續」（非停用說明區塊）
- [ ] 點擊後可完成 Google 同意並回到 `/auth/google/callback`，成功進入 Dashboard
- [ ] 以**新 Google 帳號**註冊可建立帳號並登入
- [ ] 以**已存在 email、未綁 Google** 的帳號嘗試 Google 登入 → 不應建立重複帳號，應提示改以密碼登入後至設定綁定
- [ ] 已登入 → 個人設定 → 連結 Google → 綁定成功
- [ ] 已設密碼的帳號可解除 Google 綁定；純 Google 帳號（無密碼）解除時應被拒絕

驗證通過後無需再改程式；若失敗請查 `GOOGLE_REDIRECT_URI` 是否與 Console 完全一致、`APP_ENV` 非 `local`、`GOOGLE_OAUTH_ENABLED=true`。

### 本機開發

- 使用電子郵件／密碼（Fortify）；登入頁會顯示 Google 不可用說明。
- 勿將 production 的 redirect URI 指到 `localhost`，除非另行在 Console 登錄且你確定要測本機 OAuth（本專案預設仍由 `local` 環境關閉）。

### 新增資料庫物件

Migration（依序執行；`php artisan migrate` 會自動套用尚未執行的檔案）：

1. `2026_06_01_141232_create_conversation_summaries_table.php`
2. `2026_06_01_141730_add_summarize_lock_columns_to_conversation_summaries_table.php`
3. `2026_06_01_152744_add_google_columns_to_users_table.php`（`google_id`、token 欄位；`password` nullable）

表 `conversation_summaries` 欄位重點：

- `chat_room_id`（unique，每房一筆切點）
- `content`（小結全文）
- `summarized_up_to_message_id`
- `summarizing_at` / `summarizing_until`（小結鎖，預設 120 秒逾時）

### 行為與營運注意

- 同房可計入上下文的訊息 **> 20 則** 且視窗外仍有未小結訊息時，該次 AI 請求會**先** summarize，再串流回覆，該次回應可能較慢。
- 並發 AI 請求：僅一個請求會執行 summarize；其餘請求短暫等待後沿用既有切點，避免重複計費。
- 清除**可清空權限內**的聊天室時，會一併刪除該房 `conversation_summaries`（全域主題房目前不可清空）。
- 若 summarize 失敗，會依現有流程寫入 `sender_type=error` 訊息。

### 建議驗證（佈署後）

```bash
php artisan test --filter=ConversationContext
php artisan test tests/Feature/Controllers/ChatRoomControllerTest.php
```

或於測試房主題房累積超過 20 則 AI 對話後再發一則 AI 訊息，確認：

1. `conversation_summaries` 有該 `chat_room_id` 列
2. 回覆仍正常串流
3. 聊天列表未出現小結訊息

### Agent / Claude 佈署檢查清單

- [ ] `git pull` 完成且無衝突
- [ ] `composer install --no-dev` 完成
- [ ] `php artisan migrate --force` 成功
- [ ] `OPENAI_API_KEY` 已設定
- [ ] （可選）`php artisan config:cache` 等
- [ ] （可選）執行相關 Pest 測試
- [ ] 以實際聊天室 smoke test 一則 AI 訊息

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

### 新增資料庫物件

Migration（依序執行）：

1. `2026_06_01_141232_create_conversation_summaries_table.php`
2. `2026_06_01_141730_add_summarize_lock_columns_to_conversation_summaries_table.php`

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

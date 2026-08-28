# GPT Chat Room - 快速開始指南

## 專案快速概覽

這是一個基於 Laravel + Vue.js + Inertia.js 的 GPT 聊天室應用程式，支援與 OpenAI GPT-5-nano 進行即時對話。

## 核心檔案位置

### 後端核心檔案
- **控制器**: `app/Http/Controllers/ChatRoomController.php`
- **服務**: `app/Services/GPTService.php`
- **模型**: `app/Models/Message.php`
- **路由**: `routes/web.php`
- **配置**: `config/openai.php`

### 前端核心檔案
- **聊天室頁面**: `resources/js/Pages/ChatRoom.vue`
- **佈局**: `resources/js/Layouts/AppLayout.vue`
- **主要樣式**: `resources/css/app.css`

### 資料庫
- **遷移檔案**: `database/migrations/2024_07_07_064000_create_messages_table.php`
- **資料庫檔案**: `database/database.sqlite`

## 快速開發指令

```bash
# 安裝依賴
composer install && npm install

# 環境設定
cp .env.example .env
php artisan key:generate

# 資料庫設定
php artisan migrate

# 開發模式
php artisan serve & npm run dev
```

## 主要功能流程

### 1. 聊天流程
1. 使用者輸入訊息 → `ChatRoom.vue`
2. 發送到後端 → `ChatRoomController::sendMessageStream()`
3. 呼叫 GPT API → `GPTService::sendMessageStream()`
4. 流式回應 → SSE 推送到前端
5. 儲存訊息 → `Message` 模型

### 2. 認證流程
- 使用 Laravel Jetstream 認證
- 所有聊天功能需要登入
- 支援雙因子認證

## 關鍵技術實現

### 流式回應 (SSE)
```php
// 後端：ChatRoomController::sendMessageStream()
return response()->stream(function () use ($stream) {
    foreach ($stream as $response) {
        echo "data: " . json_encode(['content' => $content]) . "\n\n";
        ob_flush();
        flush();
    }
}, 200, [
    'Content-Type' => 'text/event-stream',
    'Cache-Control' => 'no-cache',
]);
```

```javascript
// 前端：ChatRoom.vue
const response = await axios({
    method: 'POST',
    url: route('chat.send-message-stream'),
    data: { message: messageContent },
    responseType: 'text',
    onDownloadProgress: (progressEvent) => {
        // 處理流式資料
    }
});
```

### 訊息儲存
```php
// Message 模型
protected $fillable = ['user_id', 'text', 'sender_type'];

// 發送者類型：'user', 'gpt', 'error'
```

## 環境變數設定

```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_ORGANIZATION=your_organization_id_here
OPENAI_MODEL=gpt-5.6-luna
OPENAI_REQUEST_TIMEOUT=30
```

## 常見開發任務

### 新增功能
1. 在 `ChatRoomController` 新增方法
2. 在 `routes/web.php` 新增路由
3. 在 Vue 組件實現前端邏輯

### 修改 UI
1. 編輯 `ChatRoom.vue`
2. 使用 Tailwind CSS 類別
3. 執行 `npm run dev`

### 資料庫變更
1. `php artisan make:migration`
2. `php artisan migrate`

## 除錯要點

- **API 錯誤**: 檢查 OpenAI API 金鑰
- **流式問題**: 檢查瀏覽器 Network 標籤
- **認證問題**: 確認使用者已登入

## 測試指令

```bash
# 執行測試
php artisan test

# 程式碼格式化
./vendor/bin/pint

# 建構生產版本
npm run build
```

---

這個快速指南提供了開發所需的核心資訊，讓 AI 能夠快速上手專案開發。

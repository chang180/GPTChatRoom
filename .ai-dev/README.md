# GPT Chat Room - AI 開發指南

## 專案概述

GPT Chat Room 是一個現代化的即時聊天應用程式，讓使用者可以與 OpenAI 的 GPT-5-nano 模型進行對話。本專案採用 Laravel + Vue.js + Inertia.js 的全端解決方案。

### 核心功能
- 🤖 AI 聊天：與 OpenAI GPT-5-nano 進行智能對話
- 💬 多聊天室系統：4個主題聊天室（工作、學習、創意、日常）
- 🔄 即時聊天：流暢的對話體驗，支援 Markdown 格式回應
- 📝 訊息分類：支援「直接發送」和「AI 發問」兩種訊息類型
- 🔐 完整認證：Laravel Jetstream 提供使用者註冊、登入、雙因子認證
- 🌙 Dark Mode：完整的暗黑模式支援
- 📱 響應式設計：適配桌面和行動裝置
- 📝 訊息歷史：自動儲存和載入聊天記錄
- ⚡ 流式回應：支援 Server-Sent Events (SSE) 即時流式回應

## 技術架構

### 後端技術棧
- **Laravel 12.0** - PHP 框架
- **Laravel Jetstream** - 認證與團隊管理
- **Laravel Sanctum** - API 認證
- **SQLite** - 資料庫
- **OpenAI PHP SDK** - AI 服務整合

### 前端技術棧
- **Vue.js 3.3** - 前端框架
- **Inertia.js 2.0** - 現代化的單頁應用
- **Tailwind CSS 3.4** - CSS 框架
- **Vite 6.2** - 建構工具
- **Marked.js** - Markdown 渲染

### 開發工具
- **Laravel Nightwatch** - 監控與日誌
- **Pest** - 測試框架
- **Laravel Pint** - 程式碼格式化

## 專案結構

```
GPTChatRoom/
├── app/
│   ├── Http/Controllers/
│   │   ├── ChatRoomController.php    # 聊天室控制器（支援多聊天室）
│   │   └── HomeController.php        # 首頁控制器
│   ├── Models/
│   │   ├── User.php                  # 使用者模型
│   │   ├── Message.php               # 訊息模型（關聯聊天室）
│   │   └── ChatRoom.php              # 聊天室模型
│   ├── Services/
│   │   ├── GPTService.php            # GPT API 服務
│   │   └── MessageCacheService.php   # 訊息快取服務
│   └── Providers/
│       └── GPTServiceProvider.php    # GPT 服務提供者
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── ChatRoom.vue          # 聊天室頁面
│   │   │   └── Dashboard.vue         # 儀表板
│   │   ├── Components/               # Vue 組件
│   │   └── Layouts/
│   │       └── AppLayout.vue         # 應用程式佈局
│   └── css/
│       └── app.css                   # 主要樣式
├── database/
│   ├── migrations/                   # 資料庫遷移檔案
│   └── database.sqlite               # SQLite 資料庫
├── routes/
│   └── web.php                       # 網頁路由
└── config/
    └── openai.php                    # OpenAI 配置
```

## 核心檔案說明

### 1. 聊天室控制器 (`app/Http/Controllers/ChatRoomController.php`)
- `index()` - 顯示聊天室頁面
- `client()` - 顯示聊天室客戶端頁面
- `sendMessage()` - 發送訊息（非流式）
- `sendMessageStream()` - 發送訊息（流式回應）

### 2. GPT 服務 (`app/Services/GPTService.php`)
- `sendMessage()` - 發送訊息到 OpenAI API
- `sendMessageStream()` - 流式發送訊息到 OpenAI API

### 3. 訊息模型 (`app/Models/Message.php`)
- 儲存聊天訊息
- 關聯使用者
- 支援不同發送者類型（user, gpt, error）

### 4. 聊天室頁面 (`resources/js/Pages/ChatRoom.vue`)
- Vue 3 Composition API
- 流式訊息處理
- 即時 UI 更新
- 錯誤處理和取消請求功能

## 資料庫結構

### messages 表
```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    text TEXT NOT NULL,
    sender_type VARCHAR(255) NOT NULL, -- 'user', 'gpt', 'error'
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## API 端點

### 認證路由（需要登入）
- `GET /chat` - 聊天室頁面
- `GET /chat-client` - 聊天室客戶端頁面
- `POST /chat/send-message` - 發送訊息（非流式）
- `POST /chat/send-message-stream` - 發送訊息（流式）

### 公開路由
- `GET /` - 首頁

## 環境配置

### 必要的環境變數
```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_ORGANIZATION=your_organization_id_here
OPENAI_REQUEST_TIMEOUT=30
```

### 資料庫配置
- 使用 SQLite 資料庫
- 資料庫檔案：`database/database.sqlite`

## 開發工作流程

### 1. 本地開發環境設定
```bash
# 安裝後端依賴
composer install

# 安裝前端依賴
npm install

# 複製環境檔案
cp .env.example .env

# 生成應用程式金鑰
php artisan key:generate

# 執行資料庫遷移
php artisan migrate

# 建構前端資源
npm run build
# 或開發模式
npm run dev
```

### 2. 啟動開發伺服器
```bash
# 啟動後端伺服器
php artisan serve

# 啟動前端建構（開發模式）
npm run dev
```

### 3. 測試
```bash
# 執行測試
php artisan test
```

### 4. 程式碼格式化
```bash
# 格式化 PHP 程式碼
./vendor/bin/pint
```

## 核心功能實現

### 1. 流式訊息處理
- 使用 Server-Sent Events (SSE) 實現即時流式回應
- 前端使用 `axios` 的 `onDownloadProgress` 處理流式資料
- 支援取消正在進行的請求

### 2. 訊息儲存
- 使用者訊息和 GPT 回應都會儲存到資料庫
- 支援訊息歷史載入（最近 50 條）
- 使用 `sender_type` 區分不同類型的訊息

### 3. 錯誤處理
- API 錯誤會顯示為系統訊息
- 支援網路錯誤、API 限制等各種錯誤情況
- 提供使用者友善的錯誤訊息

### 4. 認證與授權
- 使用 Laravel Jetstream 提供完整的認證系統
- 所有聊天功能都需要登入
- 支援雙因子認證

## 常見開發任務

### 1. 新增功能
1. 在 `ChatRoomController` 中新增方法
2. 在 `routes/web.php` 中新增路由
3. 在 Vue 組件中實現前端邏輯
4. 更新資料庫結構（如需要）

### 2. 修改 UI
1. 編輯 Vue 組件檔案
2. 使用 Tailwind CSS 類別進行樣式調整
3. 執行 `npm run dev` 重新建構

### 3. 新增 API 端點
1. 在控制器中新增方法
2. 在路由檔案中註冊路由
3. 確保適當的中間件保護

### 4. 資料庫變更
1. 建立新的遷移檔案：`php artisan make:migration`
2. 執行遷移：`php artisan migrate`
3. 更新相關的 Model 檔案

## 除錯指南

### 1. 常見問題
- **OpenAI API 錯誤**：檢查 API 金鑰和組織 ID
- **流式回應問題**：檢查瀏覽器控制台的 SSE 錯誤
- **認證問題**：確認使用者已登入且 session 有效

### 2. 日誌位置
- Laravel 日誌：`storage/logs/laravel.log`
- 瀏覽器開發者工具：Network 和 Console 標籤

### 3. 測試建議
- 使用不同的瀏覽器測試
- 測試網路中斷情況
- 測試長時間的對話

## 部署注意事項

### 1. 生產環境配置
- 設定正確的 `APP_ENV=production`
- 配置適當的 `APP_DEBUG=false`
- 設定強化的 `APP_KEY`

### 2. 效能優化
- 使用 `npm run build` 建構生產版本
- 配置適當的快取策略
- 考慮使用 Redis 進行 session 儲存

### 3. 安全性
- 確保 OpenAI API 金鑰安全
- 配置適當的 CORS 政策
- 使用 HTTPS

## 擴展建議

### 1. 功能擴展
- 支援多個 AI 模型選擇
- 新增對話主題分類
- 實現訊息搜尋功能
- 新增檔案上傳功能

### 2. 技術改進
- 實現 WebSocket 即時通訊
- 新增訊息加密
- 實現離線支援
- 新增 PWA 功能

### 3. 效能優化
- ✅ 實現訊息分頁載入
- ✅ 新增訊息快取機制
- ✅ 優化大型對話的處理
- 實現背景任務處理

## 📅 開發日誌

### 2025-01-20 - 多聊天室系統實現
- ✅ 實現4個主題聊天室（工作、學習、創意、日常）
- ✅ 全局共享聊天室架構
- ✅ 前端頁籤切換功能
- ✅ 訊息分類系統（直接發送/AI 發問）
- ✅ 聊天室特定記錄清除
- ✅ Dark Mode 完整支援
- ✅ 快取優化（支援特定聊天室）
- ✅ 修復多個 bug 和用戶體驗問題

---

這個指南提供了專案的完整概覽，幫助 AI 快速理解專案結構和開發流程。如需更詳細的資訊，請參考相關的原始碼檔案。

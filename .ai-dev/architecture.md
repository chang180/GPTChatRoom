# GPT Chat Room - 系統架構文件

## 整體架構圖

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   前端 (Vue.js)  │    │   後端 (Laravel) │    │  外部服務 (OpenAI)│
│                 │    │                 │    │                 │
│ ┌─────────────┐ │    │ ┌─────────────┐ │    │ ┌─────────────┐ │
│ │ ChatRoom.vue│ │◄──►│ │ChatRoomCtrl │ │◄──►│ │ GPT-5-nano  │ │
│ │             │ │    │ │             │ │    │ │   API       │ │
│ │ - 訊息輸入   │ │    │ │ - 路由處理   │ │    │ │             │ │
│ │ - 流式顯示   │ │    │ │ - 認證檢查   │ │    │ │ - 智能回應   │ │
│ │ - 錯誤處理   │ │    │ │ - 資料驗證   │ │    │ │ - 流式回應   │ │
│ └─────────────┘ │    │ └─────────────┘ │    │ └─────────────┘ │
│                 │    │        │        │    │                 │
│ ┌─────────────┐ │    │ ┌─────────────┐ │    │                 │
│ │ AppLayout   │ │    │ │ GPTService  │ │    │                 │
│ │             │ │    │ │             │ │    │                 │
│ │ - 導航列     │ │    │ │ - API 呼叫   │ │    │                 │
│ │ - 認證狀態   │ │    │ │ - 錯誤處理   │ │    │                 │
│ └─────────────┘ │    │ └─────────────┘ │    │                 │
│                 │    │        │        │    │                 │
└─────────────────┘    │ ┌─────────────┐ │    │                 │
                       │ │   Message   │ │    │                 │
                       │ │   Model     │ │    │                 │
                       │ │             │ │    │                 │
                       │ │ - 訊息儲存   │ │    │                 │
                       │ │ - 關聯使用者 │ │    │                 │
                       │ └─────────────┘ │    │                 │
                       │        │        │    │                 │
                       │ ┌─────────────┐ │    │                 │
                       │ │   SQLite    │ │    │                 │
                       │ │  Database   │ │    │                 │
                       │ │             │ │    │                 │
                       │ │ - 使用者資料 │ │    │                 │
                       │ │ - 訊息記錄   │ │    │                 │
                       │ └─────────────┘ │    │                 │
                       └─────────────────┘    └─────────────────┘
```

## 技術架構層次

### 1. 表現層 (Presentation Layer)
- **Vue.js 3.3** - 前端框架
- **Inertia.js 2.0** - 無 API 的 SPA 體驗
- **Tailwind CSS 3.4** - 樣式框架
- **Vite 6.2** - 建構工具

### 2. 應用層 (Application Layer)
- **Laravel 12.0** - PHP 框架
- **Laravel Jetstream** - 認證系統
- **Laravel Sanctum** - API 認證

### 3. 服務層 (Service Layer)
- **GPTService** - OpenAI API 整合
- **認證服務** - 使用者管理
- **訊息服務** - 聊天邏輯

### 4. 資料層 (Data Layer)
- **SQLite** - 資料庫
- **Eloquent ORM** - 資料存取
- **遷移系統** - 資料庫版本控制

## 資料流程

### 1. 使用者發送訊息流程
```
使用者輸入 → Vue 組件 → HTTP POST → Laravel 路由 → 控制器 → GPT 服務 → OpenAI API
                ↓
資料庫儲存 ← 訊息模型 ← 控制器 ← GPT 回應 ← OpenAI API
                ↓
SSE 流式回應 → Vue 組件 → 即時顯示
```

### 2. 認證流程
```
使用者登入 → Jetstream 認證 → Sanctum Token → 中間件驗證 → 控制器存取
```

### 3. 訊息儲存流程
```
使用者訊息 → Message 模型 → SQLite 資料庫
GPT 回應 → Message 模型 → SQLite 資料庫
```

## 核心組件詳解

### 1. ChatRoomController
```php
class ChatRoomController extends Controller
{
    // 顯示聊天室頁面
    public function index()
    
    // 發送訊息（非流式）
    public function sendMessage(Request $request)
    
    // 發送訊息（流式）
    public function sendMessageStream(Request $request)
}
```

**職責**:
- 處理 HTTP 請求
- 驗證使用者認證
- 協調 GPT 服務
- 管理訊息儲存
- 處理流式回應

### 2. GPTService
```php
class GPTService
{
    // 發送訊息到 OpenAI
    public function sendMessage($message)
    
    // 流式發送訊息
    public function sendMessageStream($message)
}
```

**職責**:
- 與 OpenAI API 通訊
- 處理 API 錯誤
- 管理 API 金鑰
- 提供流式回應

### 3. Message Model
```php
class Message extends Model
{
    protected $fillable = ['user_id', 'text', 'sender_type'];
    
    public function user()
}
```

**職責**:
- 定義訊息資料結構
- 管理使用者關聯
- 提供資料存取介面

### 4. ChatRoom.vue
```vue
<script setup>
// 主要功能
const sendMessage = async () => { /* 發送訊息 */ }
const handleError = (error) => { /* 錯誤處理 */ }
const cancelRequest = () => { /* 取消請求 */ }
</script>
```

**職責**:
- 使用者介面渲染
- 訊息輸入處理
- 流式回應顯示
- 錯誤狀態管理
- 請求取消功能

## 資料庫設計

### messages 表結構
```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY AUTOINCREMENT,
    user_id BIGINT NOT NULL,
    text TEXT NOT NULL,
    sender_type VARCHAR(255) NOT NULL, -- 'user', 'gpt', 'error'
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 資料關係
- **users** ←→ **messages** (一對多)
- 每個使用者可以有多個訊息
- 訊息刪除時會級聯刪除相關記錄

## API 設計

### 路由結構
```php
// 認證路由群組
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/chat', [ChatRoomController::class, 'index']);
    Route::post('/chat/send-message', [ChatRoomController::class, 'sendMessage']);
    Route::post('/chat/send-message-stream', [ChatRoomController::class, 'sendMessageStream']);
});
```

### 請求/回應格式

#### 發送訊息請求
```json
POST /chat/send-message-stream
{
    "message": "使用者輸入的訊息"
}
```

#### 流式回應格式
```
data: {"content": "部分回應內容"}

data: {"content": "更多內容"}

data: {"done": true, "messageId": 123}
```

## 安全性考量

### 1. 認證與授權
- 使用 Laravel Sanctum 進行 API 認證
- 所有聊天功能都需要登入
- 支援雙因子認證 (2FA)

### 2. 資料驗證
- 使用 Laravel 的驗證規則
- 防止 SQL 注入攻擊
- 輸入資料清理

### 3. API 安全
- OpenAI API 金鑰環境變數保護
- 請求頻率限制
- 錯誤訊息不洩露敏感資訊

## 效能優化

### 1. 前端優化
- Vue 3 Composition API 提供更好的效能
- 使用 `reactive` 和 `ref` 進行精確的響應式更新
- 避免不必要的重新渲染

### 2. 後端優化
- 使用流式回應減少記憶體使用
- 資料庫查詢優化
- 適當的快取策略

### 3. 網路優化
- 使用 SSE 進行即時通訊
- 支援請求取消功能
- 錯誤重試機制

## 擴展性設計

### 1. 水平擴展
- 無狀態的控制器設計
- 資料庫連接池
- 負載均衡支援

### 2. 功能擴展
- 模組化的服務設計
- 可插拔的 AI 服務
- 靈活的路由結構

### 3. 技術升級
- 使用現代化的技術棧
- 遵循最佳實踐
- 完整的測試覆蓋

## 監控與日誌

### 1. 應用程式日誌
- Laravel 內建日誌系統
- 錯誤追蹤和除錯
- 效能監控

### 2. 外部監控
- Laravel Nightwatch 整合
- 系統健康檢查
- 警報機制

---

這個架構文件提供了系統的完整技術概覽，幫助開發者理解各個組件之間的關係和職責分工。

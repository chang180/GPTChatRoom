# GPT Chat Room - API 參考文件

## 概述

本文件提供 GPT Chat Room 應用程式的完整 API 參考，包括所有端點、請求格式、回應格式和錯誤處理。

## 基礎資訊

- **基礎 URL**: `http://localhost:8000` (開發環境)
- **認證方式**: Laravel Sanctum (Session-based)
- **內容類型**: `application/json`
- **字符編碼**: UTF-8

## 認證

所有聊天相關的 API 都需要使用者認證。使用 Laravel Jetstream 提供的認證系統。

### 認證流程
1. 使用者註冊/登入
2. 系統建立 session
3. 後續請求自動包含認證資訊

## API 端點

### 1. 首頁

#### GET /
獲取應用程式首頁。

**請求**:
```http
GET / HTTP/1.1
Host: localhost:8000
```

**回應**:
```html
HTTP/1.1 200 OK
Content-Type: text/html

<!-- Inertia.js 渲染的首頁 -->
```

### 2. 儀表板

#### GET /dashboard
獲取使用者儀表板頁面。

**認證**: 需要登入

**請求**:
```http
GET /dashboard HTTP/1.1
Host: localhost:8000
Cookie: laravel_session=...
```

**回應**:
```html
HTTP/1.1 200 OK
Content-Type: text/html

<!-- Inertia.js 渲染的儀表板 -->
```

### 3. 聊天室頁面

#### GET /chat
獲取聊天室頁面。

**認證**: 需要登入

**請求**:
```http
GET /chat HTTP/1.1
Host: localhost:8000
Cookie: laravel_session=...
```

**回應**:
```html
HTTP/1.1 200 OK
Content-Type: text/html

<!-- Inertia.js 渲染的聊天室頁面 -->
```

**回應資料**:
```json
{
    "messages": [
        {
            "id": 1,
            "user_id": 1,
            "text": "Hello, GPT!",
            "sender_type": "user",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:00:00.000000Z",
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            }
        }
    ],
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

### 4. 聊天室客戶端頁面

#### GET /chat-client
獲取聊天室客戶端頁面。

**認證**: 需要登入

**請求**:
```http
GET /chat-client HTTP/1.1
Host: localhost:8000
Cookie: laravel_session=...
```

**回應**: 與 `/chat` 相同

### 5. 發送訊息 (非流式)

#### POST /chat/send-message
發送訊息到 GPT 並獲取完整回應。

**認證**: 需要登入

**請求**:
```http
POST /chat/send-message HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cookie: laravel_session=...

{
    "message": "Hello, how are you?"
}
```

**請求參數**:
| 參數 | 類型 | 必填 | 描述 |
|------|------|------|------|
| message | string | 是 | 使用者輸入的訊息 |

**回應**:
```json
HTTP/1.1 200 OK
Content-Type: application/json

{
    "message": {
        "id": 1,
        "user_id": 1,
        "text": "Hello, how are you?",
        "sender_type": "user",
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    },
    "gptResponse": "Hello! I'm doing well, thank you for asking. How can I help you today?"
}
```

**錯誤回應**:
```json
HTTP/1.1 500 Internal Server Error
Content-Type: application/json

{
    "error": "API request failed: Invalid API key"
}
```

### 6. 發送訊息 (流式)

#### POST /chat/send-message-stream
發送訊息到 GPT 並獲取流式回應。

**認證**: 需要登入

**請求**:
```http
POST /chat/send-message-stream HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Accept: text/event-stream
Cache-Control: no-cache
Cookie: laravel_session=...

{
    "message": "Tell me a story"
}
```

**請求參數**:
| 參數 | 類型 | 必填 | 描述 |
|------|------|------|------|
| message | string | 是 | 使用者輸入的訊息 |

**回應**:
```
HTTP/1.1 200 OK
Content-Type: text/event-stream
Cache-Control: no-cache
Connection: keep-alive

data: {"messageId": 1}

data: {"content": "Once"}

data: {"content": " upon"}

data: {"content": " a"}

data: {"content": " time"}

data: {"content": "..."}

data: {"done": true, "messageId": 2}
```

**流式回應格式**:
- `{"messageId": number}` - 使用者訊息的 ID
- `{"content": string}` - GPT 回應的部分內容
- `{"done": true, "messageId": number}` - 回應完成，包含 GPT 訊息的 ID

**錯誤回應**:
```json
HTTP/1.1 500 Internal Server Error
Content-Type: application/json

{
    "error": "API stream request failed: Rate limit exceeded"
}
```

## 資料模型

### Message 模型

```php
class Message extends Model
{
    protected $fillable = ['user_id', 'text', 'sender_type'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

**屬性**:
| 屬性 | 類型 | 描述 |
|------|------|------|
| id | integer | 訊息唯一識別碼 |
| user_id | integer | 使用者 ID |
| text | string | 訊息內容 |
| sender_type | string | 發送者類型 ('user', 'gpt', 'error') |
| created_at | timestamp | 建立時間 |
| updated_at | timestamp | 更新時間 |

**關聯**:
- `user()` - 屬於一個使用者

### User 模型

```php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $fillable = [
        'name', 'email', 'password',
    ];
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
```

## 錯誤處理

### HTTP 狀態碼

| 狀態碼 | 描述 |
|--------|------|
| 200 | 成功 |
| 403 | 未授權 (未登入) |
| 422 | 驗證錯誤 |
| 500 | 伺服器錯誤 |

### 錯誤回應格式

```json
{
    "error": "錯誤訊息描述"
}
```

### 常見錯誤

#### 1. 認證錯誤
```json
HTTP/1.1 403 Forbidden
{
    "error": "Unauthorized"
}
```

#### 2. 驗證錯誤
```json
HTTP/1.1 422 Unprocessable Entity
{
    "message": "The given data was invalid.",
    "errors": {
        "message": ["The message field is required."]
    }
}
```

#### 3. OpenAI API 錯誤
```json
HTTP/1.1 500 Internal Server Error
{
    "error": "API request failed: Invalid API key"
}
```

#### 4. 網路錯誤
```json
HTTP/1.1 500 Internal Server Error
{
    "error": "API request failed: Connection timeout"
}
```

## 使用範例

### JavaScript (前端)

#### 發送非流式訊息
```javascript
const response = await fetch('/chat/send-message', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        message: 'Hello, GPT!'
    })
});

const data = await response.json();
console.log(data.gptResponse);
```

#### 發送流式訊息
```javascript
const response = await fetch('/chat/send-message-stream', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/event-stream',
        'Cache-Control': 'no-cache',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        message: 'Tell me a story'
    })
});

const reader = response.body.getReader();
const decoder = new TextDecoder();

while (true) {
    const { done, value } = await reader.read();
    if (done) break;
    
    const chunk = decoder.decode(value);
    const lines = chunk.split('\n');
    
    for (const line of lines) {
        if (line.startsWith('data: ')) {
            const data = JSON.parse(line.substring(6));
            if (data.content) {
                console.log('Content:', data.content);
            }
            if (data.done) {
                console.log('Done!');
                break;
            }
        }
    }
}
```

### PHP (後端測試)

```php
use App\Services\GPTService;

$gptService = new GPTService();

try {
    $response = $gptService->sendMessage('Hello, GPT!');
    echo $response['choices'][0]['message']['content'];
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

## 限制與注意事項

### 1. 速率限制
- 目前沒有實作速率限制
- 建議在生產環境中實作適當的限制

### 2. 訊息長度
- 沒有明確的訊息長度限制
- 受 OpenAI API 的 token 限制約束

### 3. 並發請求
- 支援多個並發請求
- 建議實作適當的佇列機制

### 4. 錯誤重試
- 目前沒有自動重試機制
- 建議實作指數退避重試

## 版本資訊

- **API 版本**: v1.0
- **Laravel 版本**: 12.0
- **OpenAI API 版本**: 最新版本
- **最後更新**: 2024-01-01

---

這個 API 參考文件提供了完整的端點資訊和使用範例，幫助開發者快速整合和使用 API。

# GPT Chat Room 🤖💬

<p align="center">
    <img src="public/images/gptchatroom_illustration.webp" width="400" alt="GPT Chat Room Illustration">
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.0-red?style=flat-square&logo=laravel" alt="Laravel Version">
    <img src="https://img.shields.io/badge/Vue.js-3.3-green?style=flat-square&logo=vue.js" alt="Vue.js Version">
    <img src="https://img.shields.io/badge/OpenAI-GPT--4o--mini-blue?style=flat-square&logo=openai" alt="OpenAI GPT">
    <img src="https://img.shields.io/badge/License-MIT-yellow?style=flat-square" alt="License">
</p>

## 📖 關於專案

GPT Chat Room 是一個現代化的即時聊天應用程式，讓使用者可以與 OpenAI 的 GPT-4o-mini 模型進行對話。本專案採用 Laravel + Vue.js + Inertia.js 的全端解決方案，提供流暢、響應式的聊天體驗。

### ✨ 主要特色

- 🤖 **AI 聊天**: 與 OpenAI GPT-4o-mini 進行智能對話
- 💬 **即時聊天**: 流暢的對話體驗，支援 Markdown 格式回應
- 🔐 **完整認證**: Laravel Jetstream 提供使用者註冊、登入、雙因子認證
- 📱 **響應式設計**: 適配桌面和行動裝置
- 📝 **訊息歷史**: 自動儲存和載入聊天記錄
- 🎨 **現代化 UI**: 使用 Tailwind CSS 打造美觀介面
- ⚡ **SPA 體驗**: Inertia.js 提供單頁應用程式體驗

## 🛠️ 技術堆疊

### 後端
- **Laravel 12.0** - PHP 框架
- **Laravel Jetstream** - 認證與團隊管理
- **Laravel Sanctum** - API 認證
- **SQLite** - 資料庫
- **OpenAI PHP SDK** - AI 服務整合

### 前端
- **Vue.js 3.3** - 前端框架
- **Inertia.js 2.0** - 現代化的單頁應用
- **Tailwind CSS 3.4** - CSS 框架
- **Vite 6.2** - 建構工具
- **Marked.js** - Markdown 渲染

### 開發工具
- **Laravel Nightwatch** - 監控與日誌
- **Pest** - 測試框架
- **Laravel Pint** - 程式碼格式化

## 📋 系統需求

- PHP 8.2 或更高版本
- Node.js 18 或更高版本
- Composer
- npm 或 yarn

## 🚀 安裝與設定

### 1. 複製專案
```bash
git clone https://github.com/chang180/GPTChatRoom.git
cd GPTChatRoom
```

### 2. 安裝後端依賴
```bash
composer install
```

### 3. 安裝前端依賴
```bash
npm install
```

### 4. 環境設定
```bash
cp .env.example .env
php artisan key:generate
```

### 5. 配置 OpenAI API
在 `.env` 檔案中設定您的 OpenAI API 金鑰：
```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_ORGANIZATION=your_organization_id_here
```

### 6. 資料庫設定
```bash
php artisan migrate
```

### 7. 建構前端資源
```bash
npm run build
# 或開發模式
npm run dev
```

### 8. 啟動應用程式
```bash
php artisan serve
```

## 📱 使用方式

1. **註冊帳號** - 前往註冊頁面建立新帳號
2. **登入系統** - 使用您的帳號登入
3. **開始聊天** - 點擊導航列的 "ChatRoom" 開始與 GPT 對話
4. **享受對話** - 輸入訊息並享受 AI 助手的智能回應

## 🏗️ 專案架構

```
GPTChatRoom/
├── app/
│   ├── Http/Controllers/
│   │   ├── ChatRoomController.php    # 聊天室控制器
│   │   └── HomeController.php        # 首頁控制器
│   ├── Models/
│   │   ├── User.php                  # 使用者模型
│   │   └── Message.php               # 訊息模型
│   └── Services/
│       └── GPTService.php            # GPT API 服務
├── resources/
│   ├── js/Pages/
│   │   ├── ChatRoom.vue              # 聊天室頁面
│   │   └── Dashboard.vue             # 儀表板
│   └── css/
│       └── app.css                   # 主要樣式
├── database/
│   ├── migrations/                   # 資料庫遷移檔案
│   └── database.sqlite               # SQLite 資料庫
└── routes/
    └── web.php                       # 網頁路由
```

## 🎯 主要功能

### 聊天系統
- 與 GPT-4o-mini 進行對話
- 支援 Markdown 格式回應
- 自動儲存聊天歷史
- 即時載入狀態提示

### 使用者管理
- 使用者註冊與登入
- 雙因子認證（2FA）
- 個人資料管理
- 安全的 Session 管理

### 介面特色
- 全螢幕聊天體驗
- 響應式設計
- 現代化 UI/UX
- 快速載入與流暢動畫

## 🔧 開發指南

### 本地開發
```bash
# 啟動後端伺服器
php artisan serve

# 啟動前端建構（開發模式）
npm run dev
```

### 測試
```bash
# 執行測試
php artisan test
```

### 程式碼格式化
```bash
# 格式化 PHP 程式碼
./vendor/bin/pint
```

## 🤝 貢獻

歡迎貢獻！請先 fork 此專案，建立您的功能分支，並提交 Pull Request。

1. Fork 專案
2. 建立功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交變更 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 開啟 Pull Request

## 📄 授權條款

本專案採用 MIT 授權條款。詳細資訊請參閱 [LICENSE](LICENSE) 檔案。

## 📞 聯絡資訊

- 專案連結: [https://github.com/chang180/GPTChatRoom](https://github.com/chang180/GPTChatRoom)
- 問題回報: [GitHub Issues](https://github.com/chang180/GPTChatRoom/issues)

## 🙏 致謝

- [Laravel](https://laravel.com) - 強大的 PHP 框架
- [Vue.js](https://vuejs.org) - 漸進式 JavaScript 框架
- [OpenAI](https://openai.com) - AI 技術支援
- [Tailwind CSS](https://tailwindcss.com) - 實用優先的 CSS 框架

---

<p align="center">
    Made with ❤️ by <a href="https://github.com/chang180">chang180</a>
</p>

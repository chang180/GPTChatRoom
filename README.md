# GPT Chat Room 🤖💬

<p align="center">
    <img src="public/images/gptchatroom_illustration.webp" width="400" alt="GPT Chat Room Illustration">
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.0-red?style=flat-square&logo=laravel" alt="Laravel Version">
    <img src="https://img.shields.io/badge/Vue.js-3.3-green?style=flat-square&logo=vue.js" alt="Vue.js Version">
    <img src="https://img.shields.io/badge/OpenAI-GPT--4.1--nano-blue?style=flat-square&logo=openai" alt="OpenAI GPT">
    <img src="https://img.shields.io/badge/License-MIT-yellow?style=flat-square" alt="License">
</p>

## 📖 關於專案

GPT Chat Room 是一個現代化的即時聊天應用程式，讓使用者可以與 OpenAI 的 GPT-4.1-nano 模型進行對話。本專案採用 Laravel + Vue.js + Inertia.js 的全端解決方案，提供流暢、響應式的聊天體驗。

### ✨ 主要特色

- 🤖 **AI 聊天**: 與 OpenAI GPT-4.1-nano 進行智能對話
- 💬 **即時聊天**: 流暢的對話體驗，支援 Markdown 格式回應
- 🔐 **完整認證**: Laravel Jetstream 提供使用者註冊、登入、雙因子認證
- 📱 **響應式設計**: 適配桌面和行動裝置
- 📝 **訊息歷史**: 自動儲存和載入聊天記錄
- 🎨 **現代化 UI**: 使用 Tailwind CSS 打造美觀介面
- ⚡ **SPA 體驗**: Inertia.js 提供單頁應用程式體驗
- 🔄 **反向訊息排序**: 最新訊息顯示在頂部，無需手動滾動
- 📊 **流式回應**: AI 回應即時流式顯示，無需等待完整回應
- 🛡️ **錯誤處理**: 完善的錯誤處理機制，提供清晰的錯誤訊息
- 🚫 **請求取消**: 支援取消正在進行的 AI 請求

## 🛠️ 技術堆疊

### 後端
- **Laravel 12.0** - PHP 框架
- **Laravel Jetstream** - 認證與團隊管理
- **Laravel Sanctum** - API 認證
- **SQLite** - 資料庫
- **OpenAI PHP SDK** - AI 服務整合
- **Server-Sent Events** - 流式資料傳輸

### 前端
- **Vue.js 3.3** - 前端框架
- **Inertia.js 2.0** - 現代化的單頁應用
- **Tailwind CSS 3.4** - CSS 框架
- **Vite 6.2** - 建構工具
- **Marked.js** - Markdown 渲染
- **Fetch API** - 流式數據處理

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
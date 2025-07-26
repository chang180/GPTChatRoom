# 🤝 貢獻指南

感謝您對 GPT Chat Room 專案的興趣！我們歡迎所有形式的貢獻。

## 📋 貢獻類型

我們歡迎以下類型的貢獻：

- 🐛 **Bug 回報**: 發現問題並提交 Issue
- ✨ **功能建議**: 提出新功能想法
- 🔧 **程式碼改進**: 提交程式碼修復或優化
- 📚 **文件改善**: 改進文件說明
- 🎨 **UI/UX 改進**: 介面設計優化

## 🚀 開始貢獻

### 1. Fork 專案
```bash
# 1. 點擊 GitHub 頁面右上角的 Fork 按鈕
# 2. Clone 您的 fork
git clone https://github.com/YOUR_USERNAME/GPTChatRoom.git
cd GPTChatRoom
```

### 2. 設定開發環境
```bash
# 安裝後端依賴
composer install

# 安裝前端依賴
npm install

# 複製環境設定
cp .env.example .env

# 生成應用程式金鑰
php artisan key:generate

# 執行資料庫遷移
php artisan migrate
```

### 3. 建立功能分支
```bash
# 從 master 分支建立新分支
git checkout -b feature/amazing-feature

# 或修復分支
git checkout -b fix/bug-description
```

### 4. 進行開發
- 遵循現有的程式碼風格
- 添加適當的測試
- 確保程式碼通過所有測試

### 5. 提交變更
```bash
# 添加變更的檔案
git add .

# 提交變更（使用有意義的提交訊息）
git commit -m "Add amazing feature: description of changes"

# 推送到您的 fork
git push origin feature/amazing-feature
```

### 6. 建立 Pull Request
1. 前往您的 GitHub fork 頁面
2. 點擊 "New Pull Request"
3. 填寫 PR 模板
4. 等待審查和合併

## 📝 程式碼風格

### PHP (Laravel)
- 遵循 PSR-12 編碼標準
- 使用 Laravel Pint 進行格式化
```bash
./vendor/bin/pint
```

### JavaScript/Vue.js
- 使用 ES6+ 語法
- 遵循 Vue.js 官方風格指南
- 使用 Prettier 進行格式化

### CSS
- 使用 Tailwind CSS 類別
- 避免自訂 CSS 除非必要
- 保持類別名稱語義化

## 🧪 測試

### 執行測試
```bash
# 執行所有測試
php artisan test

# 執行特定測試
php artisan test --filter ChatRoomTest
```

### 撰寫測試
- 為新功能添加測試
- 確保測試覆蓋率
- 使用 Pest 測試框架

## 📋 Pull Request 檢查清單

在提交 PR 之前，請確認：

- [ ] 程式碼遵循專案風格指南
- [ ] 添加了適當的測試
- [ ] 所有測試都通過
- [ ] 更新了相關文件
- [ ] PR 描述清楚說明了變更內容
- [ ] 沒有破壞現有功能

## 🐛 回報 Bug

### Bug 回報模板
當回報 Bug 時，請包含：

1. **Bug 描述**: 簡潔描述問題
2. **重現步驟**: 詳細的重現步驟
3. **預期行為**: 您期望發生什麼
4. **實際行為**: 實際發生了什麼
5. **環境資訊**: 
   - PHP 版本
   - Node.js 版本
   - 瀏覽器版本
   - 作業系統

### 範例
```markdown
**Bug 描述**
聊天室載入時顯示空白頁面

**重現步驟**
1. 登入應用程式
2. 點擊 "ChatRoom" 連結
3. 頁面顯示空白

**預期行為**
應該顯示聊天室介面

**實際行為**
顯示空白頁面

**環境資訊**
- PHP: 8.2
- Node.js: 18.17
- Chrome: 118.0
- Windows 11
```

## ✨ 功能建議

### 功能建議模板
```markdown
**功能描述**
簡潔描述建議的功能

**問題背景**
這個功能解決什麼問題？

**建議解決方案**
詳細描述您的解決方案

**替代方案**
考慮過的其他解決方案

**額外資訊**
任何其他相關資訊
```

## 📞 聯絡方式

如果您有任何問題，歡迎通過以下方式聯絡：

- 📧 GitHub Issues
- 💬 Discussions 頁面
- 📱 專案 Wiki

## 🙏 致謝

感謝所有貢獻者讓這個專案變得更好！

---

> 記住：每個貢獻都很重要，無論大小。感謝您幫助改善 GPT Chat Room！ 🚀

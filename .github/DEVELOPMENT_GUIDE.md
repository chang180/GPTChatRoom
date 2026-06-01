# 專案開發指南

本文件旨在協助開發者快速了解並參與此專案的開發。

> **Agent 同步：** 套件版本與編碼規範以 [`.cursor/rules/laravel-boost.mdc`](../.cursor/rules/laravel-boost.mdc) 為準；總索引見 [`AGENTS.md`](../AGENTS.md)。

## 專案概覽

這是一個基於 Laravel 框架的現代化網頁應用程式。

- **後端**: Laravel 13、PHP 8.4、Jetstream 5、Sanctum 4
- **前端**: Vue 3 + `@inertiajs/vue3` 3.x；伺服端 Inertia adapter v3
- **CSS 框架**: Tailwind CSS 3
- **打包工具**: Vite 6
- **測試框架**: Pest 4
- **認證**: Laravel Jetstream + Fortify
- **程式碼風格**: Laravel Pint
- **AI**: openai-php/laravel；**即時**: Ably broadcaster

## 環境設定

在開始之前，請確保您的開發環境已安裝以下軟體：

- PHP >= 8.4
- Composer
- Node.js & NPM

**安裝步驟:**

1.  **複製 `.env` 檔案**:
    ```bash
    cp .env.example .env
    ```

2.  **安裝後端依賴**:
    ```bash
    composer install
    ```

3.  **安裝前端依賴**:
    ```bash
    npm install
    ```

4.  **產生應用程式金鑰**:
    ```bash
    php artisan key:generate
    ```

5.  **設定資料庫**:
    在您的 `.env` 檔案中設定資料庫連線資訊 (例如 `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)。

6.  **執行資料庫遷移**:
    ```bash
    php artisan migrate
    ```

## 啟動專案

您需要同時啟動後端開發伺服器和前端打包工具。

1.  **啟動 Vite 開發伺服器** (監聽前端檔案變更):
    ```bash
    npm run dev
    ```

2.  **啟動 Laravel 開發伺服器**:
    ```bash
    php artisan serve
    ```

現在您可以透過 `http://localhost:8000` 進入應用程式。

## 開發流程

### 建立新功能

- **建立 Controller**:
  ```bash
  php artisan make:controller MyController
  ```

- **建立 Model 和 Migration**:
  ```bash
  php artisan make:model MyModel -m
  ```

- **建立 Vue Component**:
  在 `resources/js/Components` 或 `resources/js/Pages` 目錄下建立新的 `.vue` 檔案。

- **建立 Form Request**:
  為了進行驗證，請建立 Form Request。
  ```bash
  php artisan make:request MyRequest
  ```

### 執行測試

本專案使用 Pest 進行測試。

- **執行所有測試**:
  ```bash
  php artisan test
  ```

- **執行特定檔案的測試**:
  ```bash
  php artisan test tests/Feature/MyTest.php
  ```

### 程式碼風格

本專案使用 Laravel Pint 來統一程式碼風格。在提交程式碼前，請執行以下指令來格式化您的 PHP 程式碼。

```bash
vendor/bin/pint
```

## 重要目錄結構

- `app/Http/Controllers`: 控制器
- `app/Models`: Eloquent 模型
- `app/Http/Requests`: 表單驗證請求
- `config`: 專案設定檔
- `database/migrations`: 資料庫遷移檔案
- `resources/js`: 前端原始碼 (Vue components, JS)
- `resources/js/Pages`: Inertia.js 頁面元件
- `resources/js/Components`: 可重用的 Vue 元件
- `routes/web.php`: 網頁路由
- `routes/api.php`: API 路由
- `tests`: Pest 測試檔案

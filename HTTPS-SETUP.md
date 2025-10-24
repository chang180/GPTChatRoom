# Laravel Sail HTTPS 設定指南

本專案已配置支援本地 HTTPS 開發環境。

## 快速開始

### 1. 安裝 mkcert

**Windows:**
```bash
choco install mkcert
```

**macOS:**
```bash
brew install mkcert
```

**Linux:**
```bash
sudo apt install libnss3-tools
wget -O mkcert https://github.com/FiloSottile/mkcert/releases/download/v1.4.4/mkcert-v1.4.4-linux-amd64
chmod +x mkcert
sudo mv mkcert /usr/local/bin/
```

### 2. 生成 SSL 證書

```bash
# 安裝 CA 到系統
mkcert -install

# 生成證書
cd docker/nginx/ssl
mkcert localhost 127.0.0.1 ::1

# 重新命名證書文件
mv localhost+2.pem localhost.pem
mv localhost+2-key.pem localhost-key.pem
```

### 3. 啟動服務

```bash
./vendor/bin/sail up -d
```

### 4. 訪問應用程式

- **主應用**: https://localhost
- **Vite HMR**: 自動透過 https://localhost:5174 運行

## 架構說明

本設定使用 Nginx 作為反向代理，處理 HTTPS 請求：

- **443 端口**: 主 Laravel 應用程式
- **5174 端口**: Vite 開發伺服器 (HMR)

## 故障排除

如果遇到問題：

1. 確認證書文件存在於 `docker/nginx/ssl/` 目錄
2. 檢查 Docker 容器是否正常運行: `./vendor/bin/sail ps`
3. 查看 Nginx 日誌: `./vendor/bin/sail logs nginx`
4. 查看 Laravel 應用日誌: `./vendor/bin/sail logs laravel.test`
5. 重新啟動服務: `./vendor/bin/sail restart`

### 常見問題

**Q: 頁面顯示空白，但 HTTP 版本正常**
A: 這是因為靜態資源使用了 HTTP URL。確保：
- 已添加信任代理配置 (`$middleware->trustProxies(at: '*')`)
- Nginx 正確設置了 `X-Forwarded-Proto: https` header

**Q: 瀏覽器顯示安全警告**
A: 確保已執行 `mkcert -install` 並重新啟動瀏覽器

## 注意事項

- 證書文件不會被提交到版本控制
- 證書有效期為 825 天
- 更換電腦時需要重新安裝 mkcert CA

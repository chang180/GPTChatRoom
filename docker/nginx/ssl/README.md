# SSL 證書配置指引

本目錄用於存放本地 HTTPS 開發所需的 SSL 證書。

## 安裝 mkcert

mkcert 是一個用於生成本地受信任證書的工具，可以避免瀏覽器安全警告。

### Windows
```bash
# 使用 Chocolatey
choco install mkcert

# 或使用 Scoop
scoop bucket add extras
scoop install mkcert

# 或直接下載執行檔
# 從 https://github.com/FiloSottile/mkcert/releases 下載 mkcert-v1.4.4-windows-amd64.exe
```

### macOS
```bash
# 使用 Homebrew
brew install mkcert

# 或使用 MacPorts
sudo port install mkcert
```

### Linux
```bash
# Ubuntu/Debian
sudo apt install libnss3-tools
wget -O mkcert https://github.com/FiloSottile/mkcert/releases/download/v1.4.4/mkcert-v1.4.4-linux-amd64
chmod +x mkcert
sudo mv mkcert /usr/local/bin/

# CentOS/RHEL/Fedora
sudo yum install nss-tools
# 然後下載並安裝 mkcert
```

## 生成證書

1. **安裝 CA 根證書到系統**:
   ```bash
   mkcert -install
   ```

2. **生成本地開發證書**:
   ```bash
   cd docker/nginx/ssl
   mkcert localhost 127.0.0.1 ::1
   ```

3. **重新命名證書文件**:
   ```bash
   mv localhost+2.pem localhost.pem
   mv localhost+2-key.pem localhost-key.pem
   ```

## 證書文件說明

- `localhost.pem` - SSL 證書文件
- `localhost-key.pem` - SSL 私鑰文件

這些文件將被 Nginx 容器掛載並用於 HTTPS 連接。

## 驗證證書

生成完成後，您可以通過以下方式驗證證書是否正常工作：

1. 啟動 Sail 服務: `./vendor/bin/sail up -d`
2. 訪問 `https://localhost`
3. 檢查瀏覽器地址欄是否顯示鎖定圖標，且沒有安全警告

## 注意事項

- 這些證書文件已被添加到 `.gitignore`，不會被提交到版本控制
- 證書有效期為 825 天，到期後需要重新生成
- 如果更換電腦或重新安裝系統，需要重新執行 `mkcert -install`

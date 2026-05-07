# Phase 2 Checklist

本文件把 Ably Phase 1 完成後的下一階段工作拆成可執行清單。目標不是大改架構，而是在目前可用的多人同步基礎上，補齊產品與實作上的缺口。

## 目標

- 穩定目前的共享聊天室同步體驗
- 收斂「全域共享聊天室」的產品規則
- 補齊多人場景下容易出錯的互動細節
- 為未來的 presence / typing / 權限模型做準備

## Phase 2.1

### 1. 明確定義聊天室權限

- 決定誰可以清空聊天室
- 決定是否保留「任何登入使用者都可清空同房訊息」
- 若不保留，新增最小權限規則

建議：

- 學習專案可先限制為只有管理者或房主可清空
- 若暫時不做角色系統，至少在文件中明寫這是全域共享操作

### 2. 補上聊天室同步測試

- 為 broadcast event 增加 feature test
- 驗證 direct message 會 dispatch 正確事件
- 驗證 AI final reply 會 dispatch 正確事件
- 驗證 clear action 會 dispatch 正確事件

建議：

- 不測 Ably 第三方服務本身
- 只測 Laravel event dispatch 與 payload 結構

### 3. 收斂 `.env` / `.env.build` / 文件中的即時設定

- 確認 Ably 相關變數命名一致
- 確認沒有殘留舊的 `VITE_ABLY_PUBLIC_KEY` 或類似設定
- 在 README 補一段「revocable key 需要 `ABLY_TOKEN_EXPIRY <= 3600`」

## Phase 2.2

### 4. Presence 規劃

- 決定是否要顯示房內在線人數
- 決定是否需要使用者名單
- 若要做，需從 public channel 升級為 presence channel

注意：

- 這一步會連動房間授權模型
- 若尚未定義誰能進哪些房，不建議急著做

### 5. Typing indicator 規劃

- 定義 typing 事件格式
- 定義 debounce / timeout 行為
- 決定是否只在同房顯示

建議：

- 先只顯示「有人正在輸入」
- 不急著做到逐人精確標示

### 6. 已讀或最後活動時間

- 評估是否真的需要
- 若只是學習專案，可排在 presence / typing 之後

## Phase 2.3

### 7. 房間模型升級評估

- 評估是否維持 4 個固定全域主題房
- 評估是否要支援使用者自建房間
- 評估是否要導入私人房 / 邀請制房間

若要做上述任一項，屆時應同步調整：

- channel 類型：public -> private / presence
- channel 命名策略
- `routes/channels.php` 權限規則
- `clearChatRoom()` 的授權與作用範圍

## 建議順序

1. 先補 broadcast 測試
2. 再明確定義清空聊天室權限
3. 接著規劃 presence / typing
4. 最後才評估房間模型升級

## 不建議現在做的事

- 把 AI token 串流從 SSE 改成 WebSocket
- 為了做 presence 而過早把整套 channel 全部改回 private
- 在還沒有房間授權模型前，先做複雜的成員管理

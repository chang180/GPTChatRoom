# 開發文件

這個目錄整理目前專案的真實結構、已完成能力，以及下一階段的即時通訊規劃。

## 文件索引

- [`architecture.md`](architecture.md): 專案結構、資料流、核心檔案、資料模型
- [`realtime-websocket-plan.md`](realtime-websocket-plan.md): 現況分析、外部服務選型、WebSocket 導入計畫

## 專案現況摘要

- 架構：Laravel 12 + Inertia.js + Vue 3
- 資料庫：SQLite
- 聊天室：4 個固定全域主題聊天室
- AI 回應：OpenAI `gpt-5-nano`
- 即時體驗：目前只有「同一位使用者送出後立即看到回應」的 SSE 串流，不是多使用者共享的 WebSocket 同步

## 本階段已完成

- Jetstream 認證流程
- 聊天頁與主題聊天室切換
- 訊息儲存與歷史分頁
- 直接訊息與 AI 問答兩種模式
- GPT 串流回覆
- 聊天室清除

## 本階段尚未完成

- 多使用者同房即時同步
- 在線狀態 / typing / 已讀等 presence 類功能
- 廣播事件與前端 Echo 訂閱
- 與第三方 WebSocket 服務整合

## 建議閱讀順序

1. 先讀 [`architecture.md`](architecture.md)
2. 再讀 [`realtime-websocket-plan.md`](realtime-websocket-plan.md)
3. 實作時同步參考 [`.ai-dev/README.md`](../.ai-dev/README.md)

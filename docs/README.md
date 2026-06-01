# 開發文件

這個目錄整理目前專案的真實結構、已完成能力，以及下一階段的即時通訊規劃。

## 文件索引

- [`architecture.md`](architecture.md): 專案結構、資料流、核心檔案、資料模型
- [`deployment.md`](deployment.md): **生產環境 pull 佈署必讀**（migration、OpenAI、小結切點）
- [`realtime-websocket-plan.md`](realtime-websocket-plan.md): 現況分析、外部服務選型、WebSocket 導入計畫
- [`phase-2-checklist.md`](phase-2-checklist.md): Ably Phase 1 之後的下一階段工作清單

## 專案現況摘要

- 架構：Laravel 13 + Inertia.js + Vue 3
- 資料庫：SQLite
- 聊天室：4 個固定全域主題聊天室
- AI 回應：OpenAI `gpt-5-nano`
- 即時體驗：AI 串流使用 SSE，多使用者共享聊天室同步使用 Ably WebSocket

## 本階段已完成

- Jetstream 認證流程
- 聊天頁與主題聊天室切換
- 訊息儲存與歷史分頁
- 直接訊息與 AI 問答兩種模式
- GPT 串流回覆
- 最近聊天室訊息的 AI 上下文
- 超過 20 則時的增量對話小結切點（含 DB 鎖）
- 聊天室清除
- Ably broadcasting / Echo Phase 1
- 多瀏覽器即時同步驗證

## 本階段尚未完成

- 在線狀態 / typing / 已讀等 presence 類功能
- 更細的房間授權與成員模型

## 建議閱讀順序

1. 先讀 [`architecture.md`](architecture.md)
2. 再讀 [`realtime-websocket-plan.md`](realtime-websocket-plan.md)
3. 接著看 [`phase-2-checklist.md`](phase-2-checklist.md)
4. 實作時同步參考 [`.ai-dev/README.md`](../.ai-dev/README.md)

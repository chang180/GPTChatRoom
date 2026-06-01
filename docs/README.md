# 開發文件

這個目錄整理目前專案的真實結構、已完成能力，以及下一階段的即時通訊規劃。

## 文件索引

- [`architecture.md`](architecture.md): 專案結構、資料流、核心檔案、資料模型
- [`deployment.md`](deployment.md): **生產環境 pull 佈署必讀**（migration、OpenAI、小結切點、Google OAuth、**Ably 即時廣播**）
- [`realtime-websocket-plan.md`](realtime-websocket-plan.md): 現況分析、外部服務選型、WebSocket 導入計畫
- [`phase-2-checklist.md`](phase-2-checklist.md): Ably Phase 1 之後的下一階段工作清單

## 專案現況摘要

- 架構：Laravel 13 + Inertia.js + Vue 3
- Agent 規範：[`../AGENTS.md`](../AGENTS.md)
- 資料庫：SQLite
- 聊天室：4 個固定全域主題聊天室 + 邀請制私人小群組（最多 20 人）
- 認證：Jetstream + Google OAuth（本機 `local` 關閉，佈署環境啟用）
- AI 回應：OpenAI `gpt-5-nano`
- 即時體驗：AI 串流 SSE；房間事件 Ably（主題 public channel、私人 private channel）

## 本階段已完成

- Jetstream 與 Google OAuth（設定頁綁定／解除）
- 聊天頁、主題房與私人房側欄切換（`ChatSidebar`）
- 私人房建立、邀請連結、accept 流程
- 訊息儲存與歷史分頁、direct / AI 模式、GPT 串流、對話小結切點
- 聊天室清除（依房型 Policy）
- Ably broadcasting / Echo（`Echo.channel` vs `Echo.private`）
- 後端 Feature 測試（含 `GoogleAuthTest`、`PrivateChatRoomTest`）

## 本階段尚未完成

- 在線狀態 / typing / 已讀等 presence 類功能
- 私人房成員管理 UI（移除成員等；API 已存在）
- 佇署環境：Google OAuth 與 Ably 多人同步需人類端到端驗證（見 [`deployment.md`](deployment.md)；本機預設不連 Ably）

## 建議閱讀順序

1. 先讀 [`architecture.md`](architecture.md)
2. 再讀 [`realtime-websocket-plan.md`](realtime-websocket-plan.md)
3. 接著看 [`phase-2-checklist.md`](phase-2-checklist.md)
4. 實作時同步參考 [`.ai-dev/README.md`](../.ai-dev/README.md)

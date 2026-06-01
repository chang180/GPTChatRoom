# Phase 5 Handoff: 整合驗證與文件對齊

> **範圍：** 僅 Phase 5（驗收、文件、可選測試補強）。完成後更新 [`progress.md`](progress.md) 並 **STOP**。  
> **規格：** [`plan.md`](plan.md) § Phase 5、Verification Plan。  
> **前置：** Phase 1–4 ✅（含 Phase 4 Review PASS）。  
> **本階段為 private-room 最後一個實作 Phase**；完成後進入**全專案 Review**。

---

## 給執行 agent 的任務說明（可整段複製）

```text
你在 GPTChatRoom 專案執行 private-room 的 Phase 5 ONLY（整合驗證與文件）。

必讀：
- .ai-dev/private-room/phase-5-handoff.md（本檔）
- .ai-dev/private-room/plan.md（Verification Plan）
- progress.md Phase 1–4 執行回報與 Issues

硬性規則：
1. 以驗收與文件對齊為主；不要新增產品功能（成員管理 UI、presence 等不在本 Phase）。
2. 執行 php artisan test、npm run build；可補 1–2 個測試若發現文件與行為不一致。
3. 更新 .ai-dev/README.md「真實功能狀態」：私人房、Google OAuth、主題 public / 私人 private channel。
4. 視需要小幅更新 docs/architecture.md 或 docs/README.md（與現況一致即可，不大改）。
5. 在 progress.md 寫 Phase 5 執行回報 + 手動驗收結果（能自動化的寫自動化，其餘標「待人類」）。
6. 完成後 STOP；不要開新 Phase。

禁止：新 migration、Policy 業務變更、重寫 ChatRoom.vue、部署到 production。
```

---

## 進入條件

- [ ] `progress.md`：Phase 4 ✅ 且 **Phase 4 Review ✅ PASS**
- [ ] 人類指派「執行 Phase 5」
- [ ] 本機 `php artisan migrate` 已跑完 Phase 3 migration

---

## Success Criteria

- [ ] `vendor/bin/pint --dirty`（若有 PHP 變更）
- [ ] `php artisan test` 全綠
- [ ] `npm run build` 成功
- [ ] `.ai-dev/README.md` 反映：Laravel 13、Google OAuth（local 關閉）、邀請制私人房、主題房 public channel / 私人房 private channel
- [ ] `progress.md` 含 Phase 5 執行回報與 Verification Plan 對照表
- [ ] 標記 private-room **階段 B 完成**，可進入全專案 Review

---

## 實作步驟（建議順序）

| # | 任務 | 說明 |
|---|------|------|
| 1 | 跑全套自動驗證 | `pint --dirty`、`php artisan test`、`npm run build` |
| 2 | 更新 `.ai-dev/README.md` | 見下方「README 必改段落」 |
| 3 | 檢查 `docs/architecture.md` | 私人房、PrivateChannel、Google 一句話對齊（若已過時才改） |
| 4 | 確認 `ChatRoomClient.vue` | 若仍被路由使用：在 progress 註記「未支援私人房」或棄用說明；**不要**大改除非 broken |
| 5 | 可選測試 | 例如：broadcast 授權邊界、Welcome 路由 smoke（僅當發現缺口） |
| 6 | 手動驗收清單 | 複製 plan Verification Plan 到 progress，逐項勾選或標「待人類」 |
| 7 | progress 回寫 | Phase 5 執行回報 + 階段 B 全完成 |

---

## README 必改段落（`.ai-dev/README.md`）

### 「已完成」應包含

- 邀請制私人聊天室（owner、最多 20 人、複製邀請連結）
- 前端：`ChatSidebar`、私人房 `Echo.private`、Dashboard / AppLayout 入口
- Google OAuth（Phase 2；local 關閉 ADR-007）

### 「尚未完成」應移除或改寫

- 刪除「私人房前端 UI（Phase 4）」— 已完成
- 保留：presence / typing / online users、完整成員管理 UI（移除成員按鈕等）

### §「即時同步目前使用 public channel」

改為雙軌說明：

- **主題房（global_theme）：** public channel `chat-room.{id}`，任何登入者可訂閱（`routes/channels.php`）
- **私人房（private_group）：** `PrivateChannel`，僅成員；前端 `Echo.private()`

### §「多聊天室是固定主題房」

補一句：另支援使用者建立的 **private_group** 邀請制小群組，與四主題房並存。

---

## 手動驗收清單（plan.md Verification Plan）

複製到 progress Phase 5 Verification，逐項記錄結果：

| # | 項目 | 預期 | 結果欄 |
|---|------|------|--------|
| 1 | Google 註冊（**僅佈署環境**） | 新帳登入成功 | 自動/人類 |
| 2 | 同 email Google 註冊 | 不建重複帳 | 自動/人類 |
| 2b | local Google | 頁面說明 + redirect 404 | 自動（GoogleAuthTest） |
| 3 | 建立私人房 + 邀請 | accept_url 可複製 | 人類 |
| 4 | 第二帳號 accept | 同房可見訊息 | 人類 |
| 5 | 非成員 URL | 403 | 自動（PrivateChatRoomTest） |
| 6 | 主題房雙瀏覽器 | 即時同步 | 人類 + Ably |
| 7 | Dashboard / Welcome | 三入口正確 | 人類或 smoke |

**Google 1–2：** 不由 Phase 5 阻擋；沿用 Phase 2 決策 + `docs/deployment.md`。

**Ably 私頻 4、6：** 需可連 Ably 的環境；progress 標「待人類」若本 agent 無法執行。

---

## 可選程式變更（僅在發現缺口時）

- 修正文件中錯誤的路由名稱（如殘留 `route('chat')`）
- 補 `tests/Feature/*` 一兩例（勿超過必要範圍）
- **不要**為 Phase 5 新增 UI 功能

---

## progress.md 回寫（完成 Phase 5 後必做）

### 1. 階段 B 表格

```markdown
| Phase 5 整合驗證 | ✅ 完成 | YYYY-MM-DD | 見下方 Phase 5 執行回報 |
```

### 2. 新增 `## Phase 5 執行回報`

含：Success Criteria、README/docs 變更摘要、Verification 對照表、Deviations、**階段 B 總結**。

### 3. 階段 C

```markdown
| 全專案 Review | ⏳ 待指派 | Phase 5 完成後 |
```

### 4. Next Steps

- 指派全專案 Review（對照 plan + decisions + 全套測試）
- 人類：佈署後 Google + Ably 端到端

---

## 給 review agent 的驗收提示（全專案）

```text
Review GPTChatRoom private-room 全專案（Phase 1–5）。

輸入：plan.md、decisions.md、progress.md（全部 Phase 回報）、git log/diff since Phase 0

檢查：
1. ADR 001–007 是否反映在程式與 README？
2. 主題 public / 私人 private 端到端一致？
3. Google local 關閉、佈署說明存在？
4. 測試與 build 證據？

輸出：Issues、Final verdict: PASS / NEEDS_CHANGES
```

---

## 完成後 STOP

- private-room 階段 B 結束；勿開始 presence、typing 等新專案 unless 人類另開規格

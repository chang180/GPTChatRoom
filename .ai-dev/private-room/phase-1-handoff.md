# Phase 1 Handoff: Laravel 13 與依賴升級

> **範圍：** 僅 Phase 1。完成後更新 [`progress.md`](progress.md) 並 **STOP**。  
> **規格：** [`plan.md`](plan.md) § Phase 1、[`decisions.md`](decisions.md) ADR-001。  
> **下一階段 handoff：** Phase 2 完成後才會新增 `phase-2-handoff.md`（本檔勿超前實作）。

---

## 給執行 agent 的任務說明（可整段複製）

```text
你在 GPTChatRoom 專案執行 private-room 的 Phase 1 ONLY。

必讀：
- .ai-dev/private-room/phase-1-handoff.md（本檔，含 progress 回寫格式）
- .ai-dev/private-room/plan.md（Phase 1 章節）
- .ai-dev/private-room/decisions.md（ADR-001）

硬性規則：
1. 只升級 Laravel 13 與相容依賴；不做 Google OAuth、私人房、Welcome 改版。
2. 完成 plan.md Phase 1 Success Criteria 後，依 phase-1-handoff.md「progress.md 回寫」章節更新 progress.md。
3. 更新 progress.md 後立即停止，不要開始 Phase 2。
4. 執行 vendor/bin/pint --dirty；php artisan test；npm run build。
5. 開始前先列出預計修改的檔案；結束時 progress.md 的 Files Changed 必須與 git diff 一致。

交付物：可運作的 L13 專案 + 已更新的 progress.md（供 review agent 驗收）。
```

---

## 進入條件

- [ ] 人類指派「執行 Phase 1」或「依 phase-1-handoff 升級 Laravel 13」
- [ ] `progress.md` 中 Phase 1 狀態為 **未開始** 或 **進行中**（勿在 Phase 1 已標完成時重跑，除非人類要求修復）

---

## 實作步驟（依序）

| # | 動作 | 驗證 |
|---|------|------|
| 1 | 查 Jetstream / Inertia / Sanctum / Ably / Pest 對 L13 的相容性（release notes） | 阻礙寫入 progress Deviations |
| 2 | `composer require laravel/framework:^13.0`，調整相關依賴至可解析版本 | `composer install` 無誤 |
| 3 | 依 [Laravel 13 升級指南](https://laravel.com/docs/13.x/upgrade) 檢查 `bootstrap/app.php`、config、棄用 API | 應用可 boot |
| 4 | 更新 Jetstream、Inertia、Sanctum 等至 lock 相容版本 | `composer update` 成功 |
| 5 | `npm update`（僅必要時；避免無關大升級） | — |
| 6 | `vendor/bin/pint --dirty` | 無未格式化變更 |
| 7 | `php artisan test` | 全綠 |
| 8 | `npm run build` | 成功 |
| 9 | `php artisan about` | 顯示 Laravel 13.x |
| 10 | 更新 `.ai-dev/README.md`、`docs/deployment.md` 中的 Laravel 版本敘述 | 與實際版本一致 |

### 禁止（Phase 1）

- 新增 `laravel/socialite` 或 Google 相關程式
- 私人房 migration / Policy / 路由
- 修改 `ChatRoom.vue` 業務邏輯（除非 L13 升級強制編譯錯誤）
- 一次做完 Phase 2–5

---

## Success Criteria（與 plan.md 對齊）

完成前請在 progress 回寫中勾選：

- [ ] `php artisan about` → **Laravel 13.x**
- [ ] `php artisan test` → **全綠**（記錄通過數 / 失敗數）
- [ ] `npm run build` → **成功**
- [ ] `.ai-dev/README.md`、`docs/deployment.md` 版本敘述已更新

---

## 驗證指令（回寫 progress 時貼上實際輸出摘要）

```bash
php artisan about | head -20
php artisan test
npm run build
```

建議記錄：

- Laravel / PHP 版本（`about`）
- 測試：`Tests: X passed` 或失敗摘要
- `npm run build`：成功或錯誤一行摘要

---

## progress.md 回寫（完成 Phase 1 後必做）

執行 agent **必須**編輯 [`.ai-dev/private-room/progress.md`](progress.md)，保留階段 A 歷史，並更新以下區塊。

### 1. 階段 B 表格

將 Phase 1 列改為：

```markdown
| Phase 1 Laravel 13 | ✅ 完成 | YYYY-MM-DD | 見下方 Phase 1 回報 |
```

（日期填實際完成日）

### 2. 新增章節：`## Phase 1 執行回報`（若已存在則覆寫該章）

```markdown
## Phase 1 執行回報

**執行者：** （agent 名稱或 Codex / Claude 等，可自填）  
**完成日期：** YYYY-MM-DD  
**狀態：** ✅ 完成 | ⚠️ 部分完成（說明）

### Success Criteria

- [x] Laravel 13.x（實際：___）
- [x] php artisan test 全綠（___ passed, ___ failed）
- [x] npm run build 成功

### 執行摘要

（3–8 句：升級了什麼、遇到什麼、如何解決）

### Files Changed

（與 `git diff --name-only` 一致，逐行列檔）

### Verification

\`\`\`
（貼 about / test / build 關鍵輸出或摘要）
\`\`\`

### Deviations

（與 plan 不同處；無則寫 None）

### Issues / 風險留待 Phase 2

（已知問題；無則寫 None）

### Review 檢查點（給 review agent）

- [ ] composer.lock 中 laravel/framework 為 13.x
- [ ] 測試全綠證據在 Verification
- [ ] 未含 Phase 2+ 程式（Socialite、私人房 migration 等）
- [ ] README / deployment 版本已更新
```

### 3. 更新 `## Completed` 區塊

新增一行，例如：

```markdown
- YYYY-MM-DD：Phase 1 — Laravel 13 升級完成（執行者：___）
```

### 4. 更新 `## Next Steps`

```markdown
## Next Steps

1. 人類或 review agent 驗收 Phase 1（對照本檔 Success Criteria 與 progress「Phase 1 執行回報」）
2. 驗收通過後，等待 `phase-2-handoff.md` 再指派 Phase 2
3. **勿**在未指派時開始 Google OAuth
```

### 5. `## Deviations` / `## Issues`

- 升級與 plan 不符 → 寫入 **Deviations**
- 未解問題 → 寫入 **Issues**

---

## 給 review agent 的驗收提示（Phase 1）

人類完成 Phase 1 後，可將下列 prompt 交給 Cursor review（**預設不改 code**）：

```text
Review GPTChatRoom private-room Phase 1 only。

輸入：
- .ai-dev/private-room/plan.md（Phase 1）
- .ai-dev/private-room/phase-1-handoff.md
- .ai-dev/private-room/progress.md（Phase 1 執行回報章節）
- git diff

檢查：
1. progress.md 是否依 phase-1-handoff 完整回寫？
2. Success Criteria 三項是否都有證據？
3. diff 是否僅含 L13/依賴/文件，無 Socialite、私人房、OAuth UI？
4. Deviations 是否合理？

輸出：Issues、Fixes required、Final verdict: PASS / NEEDS_CHANGES
```

---

## 完成後 STOP

- 不要建立 `phase-2-handoff.md`（由規劃端在 Phase 1 驗收後提供）
- 不要改 `plan.md` / `decisions.md`，除非發現規格錯誤且人類同意
- 向人類回報：**Phase 1 完成，請 review progress.md 與 diff**

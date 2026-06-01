# Handoff: private-room（總覽）

本目錄為**唯一規格來源**。分階段 handoff 獨立成檔，**一次只交付一個 Phase**。

---

## 階段 handoff 索引

| Phase | handoff 檔案 | 狀態 |
|-------|----------------|------|
| **1** | [`phase-1-handoff.md`](phase-1-handoff.md) | ✅ 完成（Laravel 13） |
| **2** | [`phase-2-handoff.md`](phase-2-handoff.md) | ✅ 完成（Google OAuth） |
| **3** | [`phase-3-handoff.md`](phase-3-handoff.md) | ✅ 完成（私人房後端） |
| **4** | **[`phase-4-handoff.md`](phase-4-handoff.md)** | ✅ 已就緒 — **目前指派此檔** |
| 5 | `phase-5-handoff.md` | ⏳ Phase 4 驗收後 |

**執行 agent：** 只打開**當前被指派的** `phase-N-handoff.md`。

---

## STOP 規則（全 Phase 通用）

1. **一次只做一個 Phase。**
2. 完成該 Phase 的 Success Criteria 後，依該 Phase handoff 的 **「progress.md 回寫」** 更新 [`progress.md`](progress.md)，然後 **立即停止**。
3. 禁止在未指派時執行下一 Phase。
4. 禁止修改 `.cursor/plans/`（若存在）。

---

## 共用文件

| 檔案 | 用途 |
|------|------|
| [`plan.md`](plan.md) | 完整規格與各 Phase Success Criteria |
| [`decisions.md`](decisions.md) | ADR（產品與架構決策） |
| [`progress.md`](progress.md) | **各 Phase 執行後必回寫**，供 review 驗收 |

---

## Review（階段 C）

| Phase | 狀態 |
|-------|------|
| Phase 1 | ✅ PASS（2026-06-01） |
| Phase 2 | ✅ PASS（2026-06-01） |
| Phase 3 | ✅ PASS（2026-06-01） |
| Phase 4 | ⏳ 待實作完成後 review |
| 全專案 | Phase 5 後 |

---

## 常見錯誤

| 錯誤 | 正確做法 |
|------|----------|
| 私人房仍用 `Echo.channel` | `Echo.private` |
| 私人房發訊仍只傳 `theme` | 傳 `room: id` |
| Phase 4 重做 migration | 只改 Vue + 必要 props |
| 未回寫 progress.md | handoff 內有格式 |

---

## 聯絡規格

產品決策變更 → 更新 `decisions.md` + `plan.md`，並在 `progress.md` Deviations 記錄。

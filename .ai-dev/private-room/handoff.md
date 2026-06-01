# Handoff: private-room（總覽）

本目錄為**唯一規格來源**。分階段 handoff 獨立成檔。

**階段 B（Phase 1–5）已完成**（2026-06-01）。詳見 [`progress.md`](progress.md)。

---

## 階段 handoff 索引

| Phase | handoff 檔案 | 狀態 |
|-------|----------------|------|
| **1** | [`phase-1-handoff.md`](phase-1-handoff.md) | ✅ |
| **2** | [`phase-2-handoff.md`](phase-2-handoff.md) | ✅ |
| **3** | [`phase-3-handoff.md`](phase-3-handoff.md) | ✅ |
| **4** | [`phase-4-handoff.md`](phase-4-handoff.md) | ✅ |
| **5** | [`phase-5-handoff.md`](phase-5-handoff.md) | ✅ |

**新工作：** 勿再指派 Phase 1–5；佇署驗證見 `progress.md` Verification Plan；新功能另開規格。

---

## STOP 規則（全 Phase 通用）

階段 B 已結束。若修 bug 或新功能，依 [`plan.md`](plan.md) / [`decisions.md`](decisions.md) 另開任務，並更新 `progress.md` Deviations。

---

## 共用文件

| 檔案 | 用途 |
|------|------|
| [`plan.md`](plan.md) | 完整規格 |
| [`decisions.md`](decisions.md) | ADR |
| [`progress.md`](progress.md) | 各 Phase 回報 + 佇署驗收對照表 |

---

## Review（階段 C）

| Phase | 狀態 |
|-------|------|
| Phase 1–4 | ✅ PASS |
| Phase 5 | ✅ 完成（文件/自動驗證） |
| 全專案 | ⏳ 待指派 |
| 佇署 E2E | 人類（Google、Ably、邀請） |

---

## 聯絡規格

產品決策變更 → 更新 `decisions.md` + `plan.md`，並在 `progress.md` Deviations 記錄。

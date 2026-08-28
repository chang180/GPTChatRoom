# Agent guide (GPTChatRoom)

Coding agents (Cursor, Claude Code, Codex, Copilot, etc.) should follow this index. **Version-specific rules live in one canonical file** — do not guess package versions from old chat context.

## Canonical rules (read first)

| Priority | File | Notes |
|----------|------|--------|
| 1 | [`.cursor/rules/laravel-boost.mdc`](.cursor/rules/laravel-boost.mdc) | **Always apply in Cursor.** Laravel 13, Inertia server v3, Pest 4, Boost MCP, conventions. |
| 2 | [`composer.json`](composer.json) / [`composer.lock`](composer.lock) | Installed versions win over prose. |

Use the **Laravel Boost MCP** `search-docs` tool before changing Laravel/Inertia/Pest/Jetstream behavior.

## Tool-specific entry points

| Tool | Start here |
|------|------------|
| **Cursor** | `.cursor/rules/laravel-boost.mdc` + [`.cursor/CLAUDE.md`](.cursor/CLAUDE.md) |
| **Claude Code** (`.claude/`) | [`.claude/CLAUDE.md`](.claude/CLAUDE.md) — keep in sync with `.cursor/CLAUDE.md` |
| **GitHub Copilot** | [`.github/copilot-instructions.md`](.github/copilot-instructions.md) |
| **Feature / product context** | [`.ai-dev/README.md`](.ai-dev/README.md), [`docs/README.md`](docs/README.md) |

## Current stack (summary)

Upgraded **2026-08-28** (admin role + dependency refresh). Details and rules: `laravel-boost.mdc`.

- **Laravel 13** (lock v13.29.0), PHP **8.4**
- **inertiajs/inertia-laravel 3.3** + **@inertiajs/vue3 3.7**
- **Jetstream 5**, **Fortify**, **Sanctum 4**
- **Pest 4**, **openai-php/laravel 0.19**
- **Reverb** broadcasting（`pusher-php-server` + Echo）, **Vite 7**, **@inertiajs/vite 3**, **Vue 3**, **Tailwind 3**

## Private-room phased development

Do **not** implement the full roadmap in one session.

- Specs: [`.ai-dev/private-room/plan.md`](.ai-dev/private-room/plan.md)
- Active handoff: e.g. [`phase-2-handoff.md`](.ai-dev/private-room/phase-2-handoff.md) (see [`handoff.md`](.ai-dev/private-room/handoff.md) index)
- After each phase: update [`progress.md`](.ai-dev/private-room/progress.md) and **STOP**

## Sync checklist (when upgrading framework/packages)

1. `composer update` / lock file
2. Edit **`.cursor/rules/laravel-boost.mdc`** (Foundational Context + `laravel/v13`, `inertia-laravel/v3`, Pest sections)
3. Mirror versions in: `AGENTS.md` (this file), `.cursor/CLAUDE.md`, `.claude/CLAUDE.md`, `.github/copilot-instructions.md`, `.cursor/project-analysis.md`, `.github/DEVELOPMENT_GUIDE.md`, root `README.md`, `docs/*` as needed
4. Run `php artisan test` && `npm run build`

## Commands (quick reference)

```bash
composer install
npm install
php artisan test
vendor/bin/pint --dirty
npm run build
npm run dev
```

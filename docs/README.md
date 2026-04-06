# Product Planning Docs

This folder contains planning assets prepared for `Hi.EventsCM` based on a competitive product review against Ticket Tailor and the current `Hi.Events` Laravel codebase.

## Contents

- [`PRODUCT_ROADMAP_2026.md`](./PRODUCT_ROADMAP_2026.md) — ranked 12-month roadmap, priority rationale, and feature backlog
- [`GITHUB_PROJECT_BOARD_TEMPLATE.md`](./GITHUB_PROJECT_BOARD_TEMPLATE.md) — recommended GitHub Projects board structure, fields, views, and starter cards

## Publishing the backlog to GitHub

If GitHub Issues are enabled for your repository, you can publish the enhancement backlog with:

```powershell
pwsh ./scripts/create-product-enhancement-issues.ps1
```

> If the script reports that issues are disabled, enable **Issues** in the repository settings first.

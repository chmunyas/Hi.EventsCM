# GitHub Projects Board Template for `Hi.EventsCM`

This document provides a recommended **GitHub Projects** structure for managing the 2026 roadmap and feature backlog.

---

## Recommended Board Name

**`Hi.EventsCM — 2026 Product Roadmap`**

---

## Recommended Columns / Statuses

Use the following workflow columns:

1. **Backlog**
2. **Ready**
3. **In Progress**
4. **In Review**
5. **Blocked**
6. **Done**

### Suggested meaning
- **Backlog** — validated ideas not yet scheduled
- **Ready** — sufficiently defined and ready for engineering pickup
- **In Progress** — active implementation
- **In Review** — QA, review, or acceptance validation
- **Blocked** — waiting on dependencies, design, decisions, or external systems
- **Done** — shipped and verified

---

## Recommended Custom Fields

Add these fields to the Project:

| Field | Type | Example values |
|---|---|---|
| `Priority` | Single select | `P0`, `P1`, `P2` |
| `Epic` | Single select | `Recurring`, `Seating`, `POS`, `Loyalty`, `Integrations`, `Ops` |
| `Area` | Single select | `Backend`, `Frontend`, `Full Stack`, `Mobile`, `Product` |
| `Quarter` | Single select | `Q2 2026`, `Q3 2026`, `Q4 2026`, `Q1 2027` |
| `Complexity` | Single select | `Low`, `Medium`, `High`, `Very High` |
| `Capability Impact` | Single select | `1`, `2`, `3`, `4`, `5` |
| `Estimate` | Text | `2 weeks`, `1 sprint`, `8–12 weeks` |
| `Owner` | Assignee | engineering or PM owner |

---

## Recommended Saved Views

### 1. **Roadmap by Quarter**
Group by: `Quarter`  
Sort by: `Priority`

### 2. **Engineering Delivery View**
Filter: `Area != Product`  
Group by: `Status`

### 3. **P0 / P1 Focus**
Filter: `Priority is P0 or P1`

### 4. **Backend-heavy Work**
Filter: `Area is Backend or Full Stack`

### 5. **Venue Expansion Initiatives**
Filter: `Epic is Seating or POS or Ops`

---

## Starter Epic Cards

Create these as the top-level items:

### Epic: Recurring Events & Timed Entry
- **Priority:** P0
- **Quarter:** Q2 2026
- **Complexity:** High
- **Area:** Full Stack

### Epic: Reserved Seating & Seating Chart MVP
- **Priority:** P0
- **Quarter:** Q3 2026
- **Complexity:** Very High
- **Area:** Full Stack

### Epic: Memberships, Season Passes & Gift Cards
- **Priority:** P1
- **Quarter:** Q4 2026
- **Complexity:** High
- **Area:** Full Stack

### Epic: Box Office / POS Mode
- **Priority:** P1
- **Quarter:** Q3–Q4 2026
- **Complexity:** High
- **Area:** Full Stack

### Epic: Buyer Self-Service
- **Priority:** P1
- **Quarter:** Q2 2026
- **Complexity:** Medium
- **Area:** Full Stack

### Epic: Analytics / CRM Integrations
- **Priority:** P2
- **Quarter:** Q1 2027
- **Complexity:** Medium
- **Area:** Backend + Frontend

### Epic: Conference / Venue Ops Pack
- **Priority:** P2
- **Quarter:** Q1 2027
- **Complexity:** Medium
- **Area:** Full Stack

---

## Suggested Starter Stories

### Under `Recurring Events & Timed Entry`
- Create database schema for event series and occurrences
- Add recurrence rule UI in event creation flow
- Add date/time selector to public checkout
- Add occurrence-level reporting filter

### Under `Reserved Seating & Seating Chart MVP`
- Create seat-map schema and reservation model
- Implement seat hold timeout service
- Build organizer seat-map editor MVP
- Add seat selection component to checkout
- Show seat metadata in ticket PDFs and check-in views

### Under `Memberships, Season Passes & Gift Cards`
- Add new product types and validation rules
- Build gift card balance and redemption flow
- Support multi-use pass validation at check-in

### Under `Box Office / POS Mode`
- Introduce staff POS session model
- Build quick-sell interface
- Add reconciliation summary report

### Under `Buyer Self-Service`
- Add ticket transfer flow
- Add self-cancel policy engine
- Add reschedule flow for recurring/timed events

---

## Suggested Labels

- `enhancement`
- `product`
- `P0`
- `P1`
- `P2`
- `laravel`
- `frontend`
- `full-stack`
- `venue`
- `ops`
- `reporting`

---

## Recommended Milestones

If using milestones in addition to Projects:

- **Q2 2026 — Scheduling Foundation**
- **Q3 2026 — Venue Expansion**
- **Q4 2026 — Monetization & Loyalty**
- **Q1 2027 — Integrations & Ops Scale**

---

## Operating Cadence

Recommended cadence:

- **Weekly:** triage backlog and unblock work
- **Biweekly:** roadmap review with engineering and product
- **Monthly:** compare shipped progress vs quarter targets
- **Quarterly:** reprioritize based on adoption and commercial pull

---

## Suggested First Board Setup Sequence

1. Create the GitHub Project
2. Add the custom fields above
3. Create the saved views
4. Import or create the epic issues
5. Link child stories to each epic
6. Assign target quarter and priority
7. Review with engineering before sprint planning

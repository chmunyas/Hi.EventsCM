# 2026 Product Roadmap for `Hi.EventsCM`

**Last updated:** April 5, 2026  
**Primary codebase focus:** `Hi.Events/` (`Laravel` backend + `React` frontend)

---

## Executive Summary

`Hi.Events` is already a strong open-source ticketing product for:

- general-admission events
- branded event pages and embeddable checkout
- promo codes, vouchers, bundles, and upsells
- QR check-in, waitlist, invoices, and webhooks

The next growth step is to evolve it into a more **venue-grade event commerce platform**.

### Top strategic opportunities
1. **Recurring events and timed-entry inventory**
2. **Reserved seating and seating charts**
3. **Memberships, season passes, and gift cards**
4. **Box office / POS workflows for in-person sales**
5. **Buyer self-service and mobile ticketing**

---

## Ranked 12-Month Roadmap

| Rank | Target Window | Enhancement | Importance | Capability | Complexity | Estimate |
|---|---|---|---|---:|---|---|
| 1 | Q2 2026 | Recurring events & timed entry | Critical | 5/5 | High | 6–10 weeks |
| 2 | Q3 2026 | Seating chart MVP | Critical | 5/5 | Very High | 12–20 weeks |
| 3 | Q4 2026 | Memberships, season passes & gift cards | High | 4/5 | High | 8–12 weeks |
| 4 | Q3–Q4 2026 | Box office / POS mode | High | 4/5 | High | 8–12 weeks |
| 5 | Q2 2026 | Buyer self-service | High | 4/5 | Medium | 4–8 weeks |
| 6 | Q4 2026 | Apple Wallet / mobile passes | Medium | 3/5 | Medium | 3–5 weeks |
| 7 | Q1 2027 | GA4, TikTok & CRM integrations | Medium | 3/5 | Medium | 4–6 weeks |
| 8 | Q1 2027 | Conference / venue ops pack | Medium | 3/5 | Medium | 4–6 weeks |

---

## Quarterly Plan

### Q2 2026 — Foundation and fast wins
**Primary focus**
- recurring events and timed-entry support
- buyer self-service improvements
- bulk attendee import and related batch actions

**Objective**
Expand the addressable market quickly without taking on the full complexity of seating first.

### Q3 2026 — Venue expansion
**Primary focus**
- seating chart MVP
- box office / POS v1
- improved manual-order workflows

**Objective**
Strengthen support for theatres, gala dinners, concerts, and day-of-event selling.

### Q4 2026 — Monetization and loyalty
**Primary focus**
- memberships
- season passes
- gift cards
- mobile pass support

**Objective**
Increase organizer retention and recurring revenue opportunities.

### Q1 2027 — Ecosystem and operational scale
**Primary focus**
- analytics and CRM integrations
- saved reports and venue ops tooling
- advanced seating follow-ons if MVP adoption is strong

**Objective**
Help organizers measure ROI, automate follow-up, and operate more efficiently.

---

## Epic Backlog

## 1. Recurring Events & Timed Entry
**Priority:** P0  
**Problem:** The product is still comparatively weak for classes, tours, attractions, museums, and multi-date experiences.

### MVP scope
- recurrence rules (`daily`, `weekly`, custom dates)
- multiple time slots per day
- occurrence-level capacity and pricing overrides
- sale window overrides
- edit one occurrence vs entire series
- occurrence-aware reporting

### Suggested implementation areas
- `backend/database/migrations/`
- `backend/app/Services/Application/Handlers/`
- `backend/app/Services/Domain/`
- `backend/app/Repository/`
- `backend/routes/api.php`

### Acceptance criteria
- [ ] recurring series can be created and managed by organizers
- [ ] time slots can be sold independently with separate inventory
- [ ] an occurrence can be cancelled or hidden without breaking the series
- [ ] reports and exports can filter by occurrence

---

## 2. Reserved Seating & Seating Chart MVP
**Priority:** P0  
**Problem:** This is the most visible venue-grade gap against Ticket Tailor.

### MVP scope
- simple 2D seating builder
- rows, tables, booths, and mixed GA areas
- seat categories (VIP, Front, Standard, Balcony)
- seat holds with timeout
- assigned seat on ticket and check-in views
- backend double-booking protection

### Suggested implementation areas
- new seat-map and reservation tables
- dedicated seat hold / reservation services
- checkout and order validation updates

### Acceptance criteria
- [ ] organizers can build and publish a simple seating chart
- [ ] buyers can select seats during checkout
- [ ] abandoned seat holds expire automatically
- [ ] check-in tools surface assigned seat information

---

## 3. Memberships, Season Passes & Gift Cards
**Priority:** P1  
**Problem:** The platform lacks reusable entitlements and loyalty products.

### MVP scope
- membership products
- season passes with tracked usage
- gift cards with balance and redemption history
- check-in validation for entitlement products

### Acceptance criteria
- [ ] organizers can configure and sell these products
- [ ] buyers can redeem them through standard checkout flows
- [ ] balances and usage are tracked accurately

---

## 4. Box Office / POS Mode
**Priority:** P1  
**Problem:** Door-sales and venue workflows still rely too heavily on generic manual-order paths.

### MVP scope
- staff-facing quick-sell screen
- cash, manual card, complimentary, and offline tender support
- session open/close and reconciliation
- order source and staff attribution

### Acceptance criteria
- [ ] common in-person sales can be completed quickly
- [ ] orders are clearly marked as POS-generated
- [ ] reconciliation by staff and shift is available

---

## 5. Buyer Self-Service
**Priority:** P1  
**Problem:** The current `My Tickets` flow is good but not yet best-in-class.

### MVP scope
- resend and redownload
- transfer ticket
- self-cancel within policy rules
- reschedule to eligible date or slot

### Acceptance criteria
- [ ] self-service actions work securely without organizer support
- [ ] refund and transfer rules are enforced consistently
- [ ] all actions are auditable

---

## 6. Apple Wallet / Mobile Pass Support
**Priority:** P2

### MVP scope
- Apple Wallet passes for eligible tickets
- event metadata, QR code, and seat info where relevant
- invalidation/update support for cancellations and changes

---

## 7. GA4, TikTok & CRM Integrations
**Priority:** P2

### MVP scope
- GA4 purchase and checkout events
- TikTok Pixel support
- Mailchimp and HubSpot sync
- improved webhook visibility and retry diagnostics

---

## 8. Conference & Venue Ops Pack
**Priority:** P2

### MVP scope
- bulk attendee import from CSV
- badge-printing support
- saved report presets
- safer bulk organizer actions

---

## PM Recommendation

If only **three major initiatives** can be funded in the next 12 months, prioritize:

1. **Recurring events and timed-entry**
2. **Reserved seating**
3. **Memberships / season passes / gift cards**

These three provide the clearest path from strong general-admission ticketing to a more differentiated and defensible event platform.

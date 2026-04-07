# GitHub Enhancement Report — `Hi.EventsCM`

**Generated:** April 5, 2026  
**Repository:** `https://github.com/chmunyas/Hi.EventsCM`

---

## Status

I verified GitHub access and attempted to publish these as enhancement issues. The repo is authenticated and reachable, but **GitHub Issues are currently disabled** for this repository.

**Verification command**

```powershell
gh issue list --repo chmunyas/Hi.EventsCM --limit 12 --state open
```

**Result**

```text
the 'chmunyas/Hi.EventsCM' repository has disabled issues
```

> Once Issues are enabled, run `pwsh ./scripts/create-product-enhancement-issues.ps1` from the repo root to publish the backlog automatically.

---

## Executive Summary

`Hi.Events` is already a strong open-source Laravel ticketing platform for:

- general admission ticketing
- promo codes, vouchers, bundles, upsells
- branded event pages and embeddable checkout
- QR code check-in, waitlist, invoicing, webhooks, reports

The main opportunity is to move it from **excellent GA ticketing** into a more **venue-grade and repeat-revenue event commerce platform**.

### Highest-value gaps vs Ticket Tailor

1. **Recurring / timed-entry events**
2. **Reserved seating + seating chart builder**
3. **Memberships / season passes / gift cards**
4. **True POS / in-person box-office workflows**
5. **Buyer self-service** for transfer, reschedule, and self-cancel

---

## Ranked 12-Month Roadmap

| Rank | Target window | Enhancement | Importance | Capability | Complexity | Delivery target |
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

## Recommended GitHub Labels

Use these labels when issues are enabled:

- `enhancement`
- `product`
- `P0`
- `P1`
- `P2`
- `laravel`
- `frontend`
- `checkout`
- `reporting`
- `venue`
- `ops`

---

# GitHub-Ready Enhancement Backlog

## 1) Enhancement: Add recurring events and timed-entry support

**Priority:** `P0`  
**Capability impact:** Very high  
**Complexity:** High  
**Estimate:** 6–10 weeks

### Problem statement
`Hi.Events` is strong for one-off general admission events, but it is still weak for classes, museum admissions, tours, attractions, workshops, and multi-date programs where event inventory needs to be managed across a recurring schedule.

### Why this matters
This is one of the biggest competitive gaps versus Ticket Tailor’s time-slot and recurring-event capabilities. Filling this gap expands TAM significantly and unlocks higher-frequency organizer use cases.

### User stories
- As an organizer, I want to create a weekly recurring event without cloning every event manually.
- As an organizer, I want to sell different time slots on the same day with separate capacities.
- As a buyer, I want to clearly select a date and time during checkout.
- As operations staff, I want reporting at both series and single-occurrence level.

### MVP scope
- Recurrence rules: `daily`, `weekly`, and custom selected dates
- Multiple time slots per day
- Occurrence-level overrides for:
  - capacity
  - pricing
  - sale start/end
  - visibility / cancellation
- Edit one occurrence or whole series
- Public event page and checkout support for date/time selection
- Occurrence-aware reporting and exports

### Phase 2 / not required for MVP
- demand heatmaps
- season calendars
- smart recurring templates
- advanced slot-specific pricing rules

### Suggested Laravel implementation
**Backend areas impacted**
- `backend/database/migrations/`
- `backend/app/Services/Application/Handlers/`
- `backend/app/Services/Domain/`
- `backend/app/Repository/`
- `backend/routes/api.php`

**Suggested entities**
- `event_series`
- `event_occurrences`
- `event_time_slots`

**Suggested services**
- `OccurrenceGenerationService`
- `OccurrenceCapacityService`
- `OccurrencePricingOverrideService`

### Frontend areas impacted
- organizer event creation wizard
- event settings screens
- public checkout flow in `frontend/src/components/routes/product-widget/`
- reporting UI for occurrence filters

### Acceptance criteria
- [ ] Organizer can create recurring event series with at least daily and weekly rules
- [ ] Organizer can add multiple sellable time slots for the same day
- [ ] Capacity and price can be overridden on a per-occurrence basis
- [ ] One occurrence can be hidden or cancelled without breaking the whole series
- [ ] Public checkout allows buyers to select date and time cleanly
- [ ] Reports and CSV exports can filter by occurrence
- [ ] Existing one-off event flows remain backward compatible

### Success metrics
- % of new events created as recurring or timed-entry
- support ticket reduction for manual duplication
- increased organizer retention in classes/tours/attractions segments

---

## 2) Enhancement: Build reserved seating and seating chart MVP

**Priority:** `P0`  
**Capability impact:** Very high  
**Complexity:** Very High  
**Estimate:** 12–20 weeks

### Problem statement
The largest visible product gap versus Ticket Tailor is the absence of reserved seating and seating-chart tooling.

### Why this matters
Without seating, `Hi.Events` is strongest for GA/nightlife/community events. With seating, it becomes significantly more competitive for:

- theatres
- concerts
- gala dinners
- stadium or arena-style events
- premium venue events with price differentiation by seat location

### Benchmark notes
Ticket Tailor’s seating tooling supports:
- simple seating charts
- sections and floors for larger venues
- seats, tables, booths, and categories
- buyer seat selection during checkout

`Hi.Events` should start with a **simple 2D MVP** before attempting advanced multi-floor / 3D work.

### MVP scope
- organizer can build a simple seating chart
- support:
  - rows
  - individual seats
  - tables
  - booths
  - mixed GA + reserved areas
- seat categories such as `VIP`, `Front`, `Standard`, `Balcony`
- seat hold timeout during checkout
- assigned seat info displayed on ticket, order summary, and check-in screens
- API-level concurrency protection to prevent double booking

### Phase 2 / later
- sections and floors
- ADA / companion seating
- seat blocking for holds / sponsors / staff
- venue templates
- visual heatmaps

### Suggested Laravel implementation
**Backend areas impacted**
- `backend/database/migrations/`
- `backend/app/Services/Domain/Order/`
- `backend/app/Services/Application/Handlers/`
- `backend/app/Repository/`

**Suggested entities**
- `seat_maps`
- `seat_sections`
- `seat_rows`
- `seats`
- `seat_holds`
- `seat_reservations`

**Suggested services**
- `SeatAvailabilityService`
- `SeatHoldService`
- `SeatReservationService`

### Frontend areas impacted
- organizer seat-map builder
- public seat selection experience in checkout
- attendee and check-in screens to display seat metadata

### Acceptance criteria
- [ ] Organizer can create a simple seat map with rows, tables, and booths
- [ ] Buyer can select specific seats during checkout
- [ ] Seat holds expire automatically if checkout is abandoned
- [ ] Double booking is blocked by backend validation and locking
- [ ] Assigned seat appears on PDFs, order summary, and check-in tools
- [ ] GA events continue working unchanged

### Success metrics
- number of seated events created
- higher average order value from premium seat categories
- new venue / theatre acquisition

---

## 3) Enhancement: Add memberships, season passes, and gift cards

**Priority:** `P1`  
**Capability impact:** High  
**Complexity:** High  
**Estimate:** 8–12 weeks

### Problem statement
The platform currently focuses on one-time event sales. It lacks reusable entitlement and repeat-revenue products.

### Why this matters
This feature set helps organizers build loyalty and recurring revenue, especially for venues, clubs, attractions, and conference communities.

### MVP scope
#### Memberships
- organizer can sell a membership product
- membership tied to organizer or a configurable event set
- active / expired state visible in buyer account or order views

#### Season passes
- multi-entry or season-long access
- usage or entitlement tracking
- validation during check-in

#### Gift cards
- fixed or flexible purchase amount
- balance, code, expiry, redemption history
- partial redemption across multiple orders

### Suggested Laravel implementation
**Backend areas impacted**
- `Product` domain / product type enums
- order validation and redemption services
- attendee / check-in entitlement checks

**Suggested entities**
- `memberships`
- `season_passes`
- `gift_cards`
- `gift_card_redemptions`

### Frontend areas impacted
- product creation screens
- checkout flow for redeeming gift value
- attendee and order views to display membership or pass state
- check-in tools for entitlement validation

### Acceptance criteria
- [ ] Organizer can create membership, season-pass, and gift-card products
- [ ] Buyer can purchase and redeem them through existing checkout
- [ ] Gift card balances update correctly after partial redemption
- [ ] Pass validation works at check-in
- [ ] Redemptions and reversals are auditable

### Success metrics
- repeat purchase rate
- % of organizers selling reusable products
- uplift in organizer retention and LTV

---

## 4) Enhancement: Introduce box office / POS mode for in-person sales

**Priority:** `P1`  
**Capability impact:** High  
**Complexity:** High  
**Estimate:** 8–12 weeks

### Problem statement
`Hi.Events` has manual-order and offline-payment foundations, but it lacks a polished box-office experience for walk-up sales and venue staff.

### Why this matters
For venues and event-day operations, speed and clarity matter. A POS mode increases on-site conversion, reduces friction for staff, and supports mixed payment environments.

### MVP scope
- fast staff-facing POS UI
- quick product lookup and quantity entry
- tender types:
  - cash
  - manual card
  - complimentary
  - offline invoice / bank transfer
- session open / close with reconciliation summary
- staff attribution and order source tracking

### Suggested Laravel implementation
Build on top of the existing manual-order flow rather than duplicating logic.

**Suggested entities**
- `pos_sessions`
- `pos_transactions`
- `cash_drawer_events`

**Suggested metadata**
- order source: `WEB`, `POS`, `PHONE`, `COMP`

### Frontend areas impacted
- organizer / event staff sales screen
- closeout reports by session, staff member, and payment type

### Acceptance criteria
- [ ] Staff can complete common POS orders in under 30 seconds
- [ ] POS orders clearly record staff user and payment type
- [ ] Complimentary and manual orders work cleanly
- [ ] End-of-shift reconciliation is available
- [ ] All POS activity is auditable

### Success metrics
- number of in-person orders processed
- reduction in manual box-office workarounds
- improved same-day sales conversion

---

## 5) Enhancement: Expand buyer self-service (transfer, reschedule, self-cancel)

**Priority:** `P1`  
**Capability impact:** High  
**Complexity:** Medium  
**Estimate:** 4–8 weeks

### Problem statement
The current `My Tickets` flow is helpful but limited. It does not yet offer best-in-class post-purchase flexibility.

### Why this matters
Self-service reduces organizer support load and improves buyer satisfaction.

### MVP scope
- resend and redownload tickets
- transfer ticket to another attendee
- self-cancel under organizer-defined rules
- reschedule to eligible dates or slots
- audit trail of buyer actions

### Suggested Laravel implementation
- extend order / attendee state transitions safely
- add token-based self-service endpoints
- implement policy rules around eligibility windows and refundability

### Frontend areas impacted
- `frontend/src/components/routes/my-tickets/`
- order summary pages
- confirmation flows and emails

### Acceptance criteria
- [ ] Buyer can resend or redownload tickets without contacting support
- [ ] Transfer flow updates attendee ownership safely
- [ ] Self-cancel honors organizer refund rules
- [ ] Reschedule works for eligible recurring/timed events
- [ ] Actions are permissioned, logged, and reversible where appropriate

### Success metrics
- reduced support requests related to ticket changes
- increased post-purchase satisfaction

---

## 6) Enhancement: Add Apple Wallet and mobile pass support

**Priority:** `P2`  
**Capability impact:** Medium  
**Complexity:** Medium  
**Estimate:** 3–5 weeks

### Problem statement
The product supports add-to-calendar and QR/PDF delivery, but not a modern mobile pass experience for attendees.

### Why this matters
Wallet passes are fast, familiar, and valuable for mobile-first event entry.

### MVP scope
- Apple Wallet support for eligible tickets
- include event metadata, QR code, date/time, and seat info when present
- accessible from order summary and email flows
- pass invalidation or update on cancellation / key ticket changes

### Acceptance criteria
- [ ] Eligible tickets can be added to Apple Wallet
- [ ] Wallet pass contains valid scan data
- [ ] Cancellations or updates invalidate or refresh passes correctly
- [ ] Existing PDF / QR delivery remains intact

---

## 7) Enhancement: Add GA4, TikTok, and CRM integrations

**Priority:** `P2`  
**Capability impact:** Medium  
**Complexity:** Medium  
**Estimate:** 4–6 weeks

### Problem statement
`Hi.Events` already supports Meta Pixel and webhooks, but attribution and CRM automation are not yet broad enough to match leading commercial products.

### MVP scope
- `GA4` event support
- TikTok Pixel support
- Mailchimp integration
- HubSpot integration
- richer webhook coverage and delivery visibility

### Suggested Laravel implementation
- extend event/account settings
- use queued sync jobs
- improve webhook retry and failure visibility

### Acceptance criteria
- [ ] Organizer can configure GA4 and TikTok IDs without code changes
- [ ] Purchase and checkout events fire correctly
- [ ] CRM sync honors buyer consent / opt-in rules
- [ ] Webhook logs clearly show success, retry, and failure state

---

## 8) Enhancement: Add conference and venue ops features

**Priority:** `P2`  
**Capability impact:** Medium  
**Complexity:** Medium  
**Estimate:** 4–6 weeks

### Problem statement
Conference-style organizer workflows are still relatively light compared with purpose-built commercial tools.

### MVP scope
- bulk attendee / comp import from CSV
- badge printing support
- saved report presets
- stronger bulk actions for messaging, refunds, and attendee management

### Acceptance criteria
- [ ] Organizer can import attendees and comps with validation feedback
- [ ] Badge layouts can be generated from attendee fields
- [ ] Frequently used reports can be saved and reused
- [ ] Bulk actions are safe, permissioned, and auditable

---

## Suggested Master Tracker Issue

When Issues are enabled, create a parent tracker issue titled:

**`Enhancement: 2026 product roadmap and competitive gap closure tracker`**

Use this checklist:

- [ ] Add recurring events and timed-entry support
- [ ] Build reserved seating and seating chart MVP
- [ ] Add memberships, season passes, and gift cards
- [ ] Introduce box office / POS mode for in-person sales
- [ ] Expand buyer self-service (transfer, reschedule, self-cancel)
- [ ] Add Apple Wallet and mobile pass support
- [ ] Add GA4, TikTok, and CRM integrations
- [ ] Add conference and venue ops features

---

## Next Step

1. Enable **Issues** in the GitHub repo settings.
2. Run:

```powershell
pwsh ./scripts/create-product-enhancement-issues.ps1
```

3. Review and optionally add labels / milestones.

---

## Final PM Recommendation

If you can only fund **three major bets** over the next 12 months, prioritize:

1. **Recurring / timed-entry events**
2. **Reserved seating**
3. **Memberships / season passes / gift cards**

These move `Hi.Events` from a strong open-source ticketing product into a much more defensible **venue-grade event commerce platform**.

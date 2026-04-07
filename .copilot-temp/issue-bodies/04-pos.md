## Summary
Introduce a dedicated box office / POS mode for staff selling tickets in person at the venue, including cash/manual card workflows, walk-up sales, comps, and reconciliation.

## Why this matters
Hi.Events already has manual order creation and offline payment support, but it still lacks a polished **door-sales / venue ops** workflow. A proper POS mode will materially improve the product for venues, festivals, and day-of-event operations.

## Proposed MVP scope
- Staff-facing POS / box-office screen optimized for speed
- Quick product search and quantity selection
- Tender types: cash, manual card, complimentary, offline invoice
- Walk-up customer details capture (minimal or full)
- Session open / close with reconciliation summary
- Source tracking for POS-created orders

## Suggested implementation notes
### Backend / Laravel
Leverage existing manual-order flow and extend it with:
- pos_sessions
- pos_transactions
- cash_drawer_events
- order source metadata (WEB, POS, PHONE, COMP)

### Frontend
- Build a simplified “sell at the door” UI
- Add organizer permissions for POS staff
- Add reconciliation reporting by staff, session, and tender type

## Acceptance criteria
- [ ] Staff can create POS orders in under 30 seconds for common flows
- [ ] POS orders clearly record payment type and staff user
- [ ] Session totals and discrepancies can be reported
- [ ] Complimentary and manual orders are supported cleanly
- [ ] POS activity is auditable

## Priority
**P1 / High**

## Complexity estimate
**High** — ~8 to 12 weeks

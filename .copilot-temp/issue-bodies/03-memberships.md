## Summary
Add membership products, season passes, and gift cards so organizers can sell repeat-access and reusable value products—not just one-time event tickets.

## Why this matters
This is a high-leverage monetization capability. It helps venues, attractions, clubs, and conference organizers generate repeat revenue and improve customer lifetime value.

## Proposed MVP scope
### Memberships
- Sell a membership product tied to one organizer or a defined set of events
- Show active / expired status
- Allow check-in validation against membership entitlements

### Season passes
- Sell multi-entry or season-long access
- Track remaining / unlimited uses
- Enforce entitlement rules during redemption

### Gift cards
- Sell gift cards with balance, code, expiry, and redemption history
- Allow partial redemption across multiple purchases

## Suggested implementation notes
### Backend / Laravel
- Add product-type support for MEMBERSHIP, SEASON_PASS, and GIFT_CARD
- New tables may include:
  - memberships
  - season_passes
  - gift_cards
  - gift_card_redemptions
- Add redemption and entitlement validation services
- Extend order, attendee, and check-in domains to recognize reusable credentials

### Frontend
- Product creation UI needs new product types and configuration panels
- Checkout / my tickets / order summary should clearly show membership or gift-card state
- Check-in flow should support pass validation and usage updates

## Acceptance criteria
- [ ] Organizer can create a membership, season pass, or gift card product
- [ ] Buyer can purchase and redeem these products through existing checkout flows
- [ ] Gift card balances update correctly after partial use
- [ ] Pass validation works in check-in tools
- [ ] Audit logs capture all redemptions and reversals

## Priority
**P1 / High**

## Complexity estimate
**High** — ~8 to 12 weeks

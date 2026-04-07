## Summary
Add Apple Wallet / mobile pass support and strengthen the attendee mobile ticket experience.

## Why this matters
Wallet tickets improve convenience, reduce friction at the door, and help reinforce a premium experience for mobile-first attendees.

## Proposed MVP scope
- Generate Apple Wallet passes for eligible tickets
- Include event, venue, date/time, QR code, and seat info where relevant
- Expose wallet actions in confirmation emails and order summary pages
- Ensure compatibility with assigned seats and season-pass style products over time

## Notes
Hi.Events already supports add-to-calendar flows and Stripe payment method ordering that references Apple Pay / Google Pay. This enhancement focuses on the **ticket/pass artifact itself**, not just payment methods.

## Acceptance criteria
- [ ] Eligible attendees can add tickets to Apple Wallet from the order summary or email flow
- [ ] Wallet pass contains valid scan data and event metadata
- [ ] Updates / cancellations can invalidate or refresh the pass as needed
- [ ] Mobile pass works alongside existing PDF / QR ticket delivery

## Priority
**P2 / Medium**

## Complexity estimate
**Medium** — ~3 to 5 weeks

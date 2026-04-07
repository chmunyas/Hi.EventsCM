## Summary
Expand the buyer self-service experience so attendees can manage their own orders without needing organizer intervention for routine changes.

## Why this matters
Hi.Events already supports ticket lookup and order viewing, but it is still behind best-in-class tools for post-purchase self-service. Improving this area reduces support load and improves attendee satisfaction.

## Proposed MVP scope
- Ticket transfer to another attendee
- Self-serve resend / re-download of tickets
- Self-cancel under organizer-defined rules
- Reschedule to another slot/date where permitted
- Clear audit log of all buyer-initiated actions

## Suggested implementation notes
### Backend / Laravel
- Extend order and attendee state transitions safely
- Add token-based self-service endpoints
- Introduce rules/policies for:
  - when transfer is allowed
  - when cancel is allowed
  - when reschedule is allowed

### Frontend
- Extend My Tickets and order summary pages
- Surface allowed actions contextually based on organizer rules
- Provide confirmation and fallback messaging when actions are unavailable

## Acceptance criteria
- [ ] Buyer can resend or download tickets without contacting support
- [ ] Transfer flow updates attendee ownership safely
- [ ] Self-cancel honors organizer rules and refund settings
- [ ] Reschedule is supported for eligible recurring/timed events
- [ ] Every action is logged for auditability

## Priority
**P1 / High**

## Complexity estimate
**Medium** — ~4 to 8 weeks

## Summary
Add conference and venue operations improvements such as badge printing, bulk attendee import, and saved reports.

## Why this matters
These are not the first features to build, but they materially improve Hi.Events for conferences, trade shows, and more operationally demanding organizers.

## Proposed MVP scope
- Bulk attendee import / comp import from CSV
- Badge-printing support and badge-template mapping
- Saved reports and favorite report presets
- Better bulk actions for refunding, messaging, and attendee management

## Suggested implementation notes
- Use existing reporting and document-template foundations where possible
- Consider printer-friendly badge layouts first, followed by integration support
- Tie imported records to audit logs to preserve operator traceability

## Acceptance criteria
- [ ] Organizer can import attendees / comps from CSV with validation feedback
- [ ] Badge layouts can be generated from attendee fields
- [ ] Common reports can be saved and reused
- [ ] Bulk operations are safe, auditable, and permission-aware

## Priority
**P2 / Medium**

## Complexity estimate
**Medium** — ~4 to 6 weeks

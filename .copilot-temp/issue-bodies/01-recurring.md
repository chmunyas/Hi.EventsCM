## Summary
Add first-class support for recurring events, timed-entry inventory, and occurrence-level overrides so Hi.Events can compete for attractions, museums, classes, workshops, and tours.

## Why this matters
This is one of the biggest product gaps versus Ticket Tailor. Hi.Events is already strong for general-admission events, but recurring/timeslot support is needed to win higher-frequency and venue-based use cases.

## Benchmark / market reference
- Ticket Tailor: recurring events, time slot events, peak/off-peak style scheduling
- Opportunity for Hi.Events: pair flexible scheduling with self-hosted and open-source differentiation

## Proposed MVP scope
- Create an **event series** with recurrence rules (daily, weekly, custom dates)
- Support **multiple time slots** per day
- Allow **occurrence-level overrides** for capacity, pricing, on-sale/off-sale windows, and visibility
- Manage **single occurrence vs whole series** edits
- Public event pages should clearly show selectable dates/times
- Reporting should support **series totals** and **occurrence-level performance**

## Suggested implementation notes
### Backend / Laravel
- Add tables such as:
  - vent_series
  - vent_occurrences
  - vent_time_slots
- Introduce services such as:
  - OccurrenceGenerationService
  - OccurrenceCapacityService
  - OccurrencePricingOverrideService
- Extend handlers / DTOs in ackend/app/Services/Application/Handlers/
- Expose new endpoints in ackend/routes/api.php

### Frontend
- Event creation flow should support **single event** vs **recurring/timed event**
- Organizer UI needs a **series calendar / occurrence management screen**
- Public checkout should show date/time selectors without breaking existing product logic

## Acceptance criteria
- [ ] Organizer can create a recurring event series with at least daily and weekly rules
- [ ] Organizer can add multiple time slots for the same day
- [ ] Capacity and price can be overridden per occurrence
- [ ] One occurrence can be hidden/cancelled without cancelling the whole series
- [ ] Reports and exports can filter by occurrence
- [ ] Existing single-event flow remains backward compatible

## Priority
**P0 / Critical**

## Complexity estimate
**High** — ~6 to 10 weeks for a solid MVP

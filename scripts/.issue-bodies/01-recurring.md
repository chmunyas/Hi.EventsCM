## Summary
Add first-class support for recurring events, timed-entry inventory, and occurrence-level overrides.

## Why this matters
This is one of the biggest competitive gaps versus Ticket Tailor and is critical for classes, tours, attractions, workshops, and multi-date programs.

## MVP scope
- recurring series rules (daily, weekly, custom dates)
- multiple time slots per day
- occurrence-level overrides for capacity, pricing, sale windows, and visibility
- edit one occurrence vs whole series
- public event page + checkout support for date/time selection
- occurrence-aware reporting

## Suggested Laravel implementation
- tables: vent_series, vent_occurrences, vent_time_slots
- services: OccurrenceGenerationService, OccurrenceCapacityService, OccurrencePricingOverrideService
- updates to handlers, DTOs, repositories, and outes/api.php

## Acceptance criteria
- [ ] recurring series can be created and managed
- [ ] time slots can be sold with separate inventory
- [ ] one occurrence can be changed or cancelled independently
- [ ] checkout cleanly supports date/time selection
- [ ] reports can filter by occurrence

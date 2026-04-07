## Summary
Build a reserved seating MVP with a simple seating chart designer for rows, tables, booths, and seat categories.

## Why this matters
This is the single biggest venue-grade capability gap versus Ticket Tailor.

## MVP scope
- simple 2D seat-map builder
- rows, individual seats, tables, booths, and mixed GA areas
- seat categories (VIP, Front, Standard, Balcony)
- seat hold timeout during checkout
- assigned seat info on ticket, order summary, and check-in views
- concurrency-safe double-booking prevention

## Suggested Laravel implementation
- tables: seat_maps, seat_sections, seat_rows, seats, seat_holds, seat_reservations
- services: SeatAvailabilityService, SeatHoldService, SeatReservationService

## Acceptance criteria
- [ ] organizers can build and publish a simple seating chart
- [ ] buyers can select seats during checkout
- [ ] abandoned seat holds expire automatically
- [ ] assigned seats appear in ticket and check-in workflows

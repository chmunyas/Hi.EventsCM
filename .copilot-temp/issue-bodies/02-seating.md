## Summary
Build a reserved seating MVP with a simple seating chart designer for rows, tables, booths, and seat categories. This is the single biggest venue-grade capability gap versus Ticket Tailor.

## Why this matters
Without reserved seating, Hi.Events remains strongest in general admission. With seating support, the platform becomes much more compelling for theatres, gala dinners, concerts, premium venue events, and allocated-seat fundraisers.

## Benchmark / market reference
Ticket Tailor's seating tool supports:
- simple charts for most seated events
- sections/floors for larger venues
- seat categories with pricing
- seats, tables, and booths

For Hi.Events, the right move is to start with a **simple 2D MVP** before tackling more advanced layouts.

## Proposed MVP scope
- Create a **simple seating chart** for events under ~1,000 seats
- Support:
  - rows
  - individual seats
  - tables / booths
  - general-admission blocks mixed with assigned seating
- Add **seat categories** (e.g. VIP, Front, Standard, Balcony)
- Add **seat hold / lock timeout** during checkout
- Print assigned seat details on ticket / order summary / check-in views
- Prevent double-booking under concurrent checkout traffic

## Suggested implementation notes
### Backend / Laravel
- Add tables such as:
  - seat_maps
  - seat_sections
  - seat_rows
  - seats
  - seat_holds
  - seat_reservations
- Services to introduce:
  - SeatAvailabilityService
  - SeatHoldService
  - SeatReservationService
- Extend order creation flow to validate and attach reserved seats

### Frontend
- Add a **seat-map builder** in organizer settings
- Add seat selection UI to the public product widget
- Surface seat metadata in attendee, order, and check-in screens

## Acceptance criteria
- [ ] Organizer can build a simple chart with rows, tables, and booths
- [ ] Buyer can select specific seats during checkout
- [ ] Seat holds expire automatically if checkout is abandoned
- [ ] Double booking is prevented at the API level
- [ ] Assigned seat appears on tickets, confirmations, and check-in tools
- [ ] Existing GA events continue to work unchanged

## Priority
**P0 / Critical**

## Complexity estimate
**Very High** — ~12 to 20 weeks across MVP + stabilization

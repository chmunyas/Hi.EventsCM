param(
    [string]$Repo = "chmunyas/Hi.EventsCM"
)

$ErrorActionPreference = "Stop"

function Test-IssuesEnabled {
    gh issue list --repo $Repo --limit 1 2>$null | Out-Null
    return ($LASTEXITCODE -eq 0)
}

if (-not (Get-Command gh -ErrorAction SilentlyContinue)) {
    Write-Error "GitHub CLI ('gh') is not installed."
}

if (-not (Test-IssuesEnabled)) {
    Write-Host "GitHub issues are currently disabled for $Repo." -ForegroundColor Yellow
    Write-Host "Enable Issues in the repository settings, then rerun this script." -ForegroundColor Yellow
    exit 1
}

$labels = gh label list --repo $Repo --json name --jq '.[].name'
if (-not ($labels -contains 'enhancement')) {
    gh label create enhancement --repo $Repo --color a2eeef --description "New feature or request" | Out-Null
}

$tmp = Join-Path $PSScriptRoot ".issue-bodies"
New-Item -ItemType Directory -Force -Path $tmp | Out-Null

$issues = @(
    @{
        Title = "Enhancement: Add recurring events and timed-entry support"
        File = "01-recurring.md"
        Body = @"
## Summary
Add first-class support for recurring events, timed-entry inventory, and occurrence-level overrides.

## Why this matters
This is one of the biggest competitive gaps versus Ticket Tailor and is critical for classes, tours, attractions, workshops, and multi-date programs.

## MVP scope
- recurring series rules (`daily`, `weekly`, custom dates)
- multiple time slots per day
- occurrence-level overrides for capacity, pricing, sale windows, and visibility
- edit one occurrence vs whole series
- public event page + checkout support for date/time selection
- occurrence-aware reporting

## Suggested Laravel implementation
- tables: `event_series`, `event_occurrences`, `event_time_slots`
- services: `OccurrenceGenerationService`, `OccurrenceCapacityService`, `OccurrencePricingOverrideService`
- updates to handlers, DTOs, repositories, and `routes/api.php`

## Acceptance criteria
- [ ] recurring series can be created and managed
- [ ] time slots can be sold with separate inventory
- [ ] one occurrence can be changed or cancelled independently
- [ ] checkout cleanly supports date/time selection
- [ ] reports can filter by occurrence
"@
    },
    @{
        Title = "Enhancement: Build reserved seating and seating chart MVP"
        File = "02-seating.md"
        Body = @"
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
- tables: `seat_maps`, `seat_sections`, `seat_rows`, `seats`, `seat_holds`, `seat_reservations`
- services: `SeatAvailabilityService`, `SeatHoldService`, `SeatReservationService`

## Acceptance criteria
- [ ] organizers can build and publish a simple seating chart
- [ ] buyers can select seats during checkout
- [ ] abandoned seat holds expire automatically
- [ ] assigned seats appear in ticket and check-in workflows
"@
    },
    @{
        Title = "Enhancement: Add memberships, season passes, and gift cards"
        File = "03-memberships.md"
        Body = @"
## Summary
Add membership products, season passes, and gift cards to support repeat revenue and reusable entitlements.

## MVP scope
- memberships tied to organizer or event group
- season passes with use tracking
- gift cards with balance and redemption history
- validation at checkout and check-in

## Suggested implementation
- new product types for `MEMBERSHIP`, `SEASON_PASS`, and `GIFT_CARD`
- tables for memberships, passes, gift cards, and redemptions

## Acceptance criteria
- [ ] organizers can configure and sell these products
- [ ] buyers can redeem them through existing checkout flows
- [ ] balances and usage are tracked accurately
"@
    },
    @{
        Title = "Enhancement: Introduce box office / POS mode for in-person sales"
        File = "04-pos.md"
        Body = @"
## Summary
Introduce a dedicated box office / POS mode for walk-up and in-person sales.

## MVP scope
- fast staff-facing POS screen
- cash, manual card, complimentary, and offline tender types
- quick-sell flow for door sales
- staff attribution and reconciliation reporting

## Suggested implementation
- extend manual-order flow
- add `pos_sessions`, `pos_transactions`, and order-source tracking

## Acceptance criteria
- [ ] staff can complete common POS sales quickly
- [ ] all transactions record source, payment type, and operator
- [ ] session reconciliation is available
"@
    },
    @{
        Title = "Enhancement: Expand buyer self-service (transfer, reschedule, self-cancel)"
        File = "05-self-service.md"
        Body = @"
## Summary
Expand post-purchase self-service so buyers can manage routine changes without organizer intervention.

## MVP scope
- resend / redownload
- transfer ticket
- self-cancel with organizer-defined rules
- reschedule to eligible dates or slots
- audit trail for buyer actions

## Acceptance criteria
- [ ] self-service actions work securely via token-based flows
- [ ] refund and transfer rules are enforced consistently
- [ ] actions are logged and visible to organizers
"@
    },
    @{
        Title = "Enhancement: Add Apple Wallet and mobile pass support"
        File = "06-wallet.md"
        Body = @"
## Summary
Add Apple Wallet support and improve mobile-first ticket delivery.

## MVP scope
- add tickets to Apple Wallet
- include event metadata, QR code, and seat data where relevant
- support updates / invalidation on cancellation

## Acceptance criteria
- [ ] eligible tickets can be saved to Apple Wallet
- [ ] wallet passes remain valid and scannable at check-in
"@
    },
    @{
        Title = "Enhancement: Add GA4, TikTok, and CRM integrations"
        File = "07-integrations.md"
        Body = @"
## Summary
Expand attribution and CRM integrations with GA4, TikTok Pixel, Mailchimp, HubSpot, and richer webhooks.

## MVP scope
- GA4 event support
- TikTok pixel support
- Mailchimp + HubSpot sync
- improved webhook visibility and retry state

## Acceptance criteria
- [ ] organizers can configure analytics and CRM integrations without code changes
- [ ] purchase and lifecycle events sync reliably
"@
    },
    @{
        Title = "Enhancement: Add conference and venue ops features"
        File = "08-ops.md"
        Body = @"
## Summary
Add operational features for conferences and venues such as bulk attendee import, badge printing, and saved reports.

## MVP scope
- CSV attendee / comp import
- badge-printing support
- saved report presets
- stronger bulk actions for organizers

## Acceptance criteria
- [ ] attendee imports validate and complete safely
- [ ] badge layouts can be generated from attendee fields
- [ ] common reports can be saved and reused
"@
    }
)

$created = @()
foreach ($issue in $issues) {
    $path = Join-Path $tmp $issue.File
    $issue.Body | Set-Content -Path $path
    $url = gh issue create --repo $Repo --title $issue.Title --label enhancement --body-file $path
    $created += $url
}

Write-Host "Created issues:" -ForegroundColor Green
$created | ForEach-Object { Write-Host " - $_" }

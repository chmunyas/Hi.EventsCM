## Summary
Expand marketing attribution and outbound integrations with GA4, TikTok Pixel, Mailchimp, HubSpot, and richer event lifecycle syncs.

## Why this matters
Organizers increasingly expect better attribution, retargeting, and CRM automation. Hi.Events already has Meta Pixel and webhooks, but stronger integration coverage is needed to match commercial platforms.

## Proposed MVP scope
- Add GA4 event support for checkout and purchase milestones
- Add TikTok Pixel support
- Add first-party integrations for Mailchimp and HubSpot
- Expand webhooks around order state, refund, check-in, and attendee updates
- Add saved/scheduled reporting for marketing and revenue teams

## Suggested implementation notes
- Extend vent_settings and account-level settings for analytics IDs and mapping options
- Use queued jobs for outbound sync reliability
- Improve webhook retry visibility and failure diagnostics

## Acceptance criteria
- [ ] Organizers can configure GA4 and TikTok tracking without code changes
- [ ] Purchase and conversion events fire correctly
- [ ] Mailchimp / HubSpot sync can subscribe or update contacts based on buyer consent
- [ ] Webhook logs provide delivery visibility and retry state

## Priority
**P2 / Medium**

## Complexity estimate
**Medium** — ~4 to 6 weeks

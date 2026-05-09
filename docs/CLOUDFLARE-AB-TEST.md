# Cloudflare A/B Test

Last updated: 2026-05-09

## Goal

Separate:
- Cloudflare proxy behavior
- Cloudflare Turnstile behavior
- origin-only WordPress/plugin behavior

## Test page

- `https://sciuuuskids.it/product/dun-dun/`

## What changed before this test

- Really Simple Security CSP has already been disabled.
- Product-review Turnstile has been changed to lazy-load only when the reviews panel is opened or interacted with.

This means a fresh page-load comparison should now focus on the variation UI before any review captcha code is pulled in.

## Recommended order

1. Baseline mobile test on proxied production
2. Same test after opening reviews
3. Temporary no-proxy comparison
4. Optional short gray-cloud window if needed

## Test 1: Proxied baseline

Use the normal live URL:
- `https://sciuuuskids.it/product/dun-dun/`

Conditions:
- real phone if possible
- private/incognito tab
- no translation extensions
- close and reopen the tab between runs

What to check before opening reviews:
- time to first tap response on size swatches
- time to first tap response on color swatches
- whether add-to-cart button state updates immediately
- whether header/nav taps still feel delayed

Expected result after the lazy-load patch:
- no Turnstile network request should be needed before the reviews area is opened

## Test 2: Proxied after opening reviews

On the same page:
- open the reviews accordion
- tap into the review form or interact with it once

What to check:
- whether `https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit` loads only now
- whether page responsiveness changes after that script loads
- whether returning to the variation controls feels worse than before

Interpretation:
- if the page is smooth before reviews and degrades after opening reviews, Turnstile is the main suspect
- if the page is already slow before reviews, the issue is elsewhere in the product-page stack

## Test 3: No-proxy comparison

Best method:
- create a temporary DNS-only hostname that points to the same origin
- example: `origin.sciuuuskids.it`

Requirements:
- valid TLS certificate for that hostname on the origin
- same WordPress/theme/plugin code as production
- no Cloudflare proxy on that hostname

Run the exact same test on:
- `https://origin.sciuuuskids.it/product/dun-dun/`

Compare:
- first swatch tap latency
- image/gallery update latency
- nav responsiveness
- review-form-open responsiveness

Interpretation:
- if proxied and origin feel the same before reviews, Cloudflare proxying is probably not the cause
- if only proxied is slow, check Cloudflare-side browser/security features next

## Test 4: Short gray-cloud window

Use only if the DNS-only hostname is inconvenient.

Method:
- temporarily set the product hostname record to DNS-only
- test immediately from mobile
- restore proxy after the run

Risks:
- bypasses Cloudflare protections during the test window
- can affect traffic if done on the main hostname

## Network log checklist

In DevTools or remote debugging:
- preserve log
- disable cache
- filter on `JS` and `Fetch/XHR`

Capture these moments separately:
- initial page load
- first swatch tap
- first review-panel open
- first Turnstile interaction

## Decision table

If this happens:
- fast before reviews, slow after reviews

Then:
- Turnstile is the primary suspect

If this happens:
- slow before reviews on both proxied and non-proxied hosts

Then:
- investigate product-page JS/main-thread pressure, not Cloudflare

If this happens:
- slow only on proxied host, even before reviews

Then:
- investigate Cloudflare browser/security behavior next

## Next technical checks if Cloudflare still looks involved

1. Browser Integrity Check off
2. Temporary WAF bypass for `/product/*`
3. Compare HTTP/2 vs HTTP/3 behavior if reproducible
4. Check whether another Cloudflare-managed challenge appears in mobile-only sessions

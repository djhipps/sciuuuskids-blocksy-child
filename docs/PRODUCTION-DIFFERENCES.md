# Production Differences

Last updated: 2026-05-09

## Purpose

This document records the production-only behavior that does not exist in the child theme alone or in the local Docker theme workspace. It should be read before debugging mobile product-page regressions, caching issues, or security-header changes.

## Production baseline

Site:
- `https://sciuuuskids.it`

Current live network path:
- visitor -> Cloudflare proxy -> Lightsail origin

Current live origin host:
- `3.64.109.156`

Current live transport observed on 2026-05-09:
- Cloudflare edge response: `HTTP/2 200`
- `server: cloudflare`
- `cf-cache-status: DYNAMIC`
- origin response via direct IP + `Host:` header: `HTTP/1.1 200`
- origin server header: `Apache`

Implication:
- the product page is not being edge-cached as static HTML by Cloudflare
- Cloudflare is still in the request path for TLS, HTTP/2/HTTP/3, and bot/security features
- debugging must distinguish origin-generated markup from Cloudflare-coupled client-side behavior

## Cloudflare live setup

As handed over in May 2026:
- Cloudflare nameservers active
- DNS moved from Lightsail DNS to Cloudflare
- `sciuuuskids.it` and `www` are proxied
- SSL/TLS mode is `Full (strict)`
- Bot Fight Mode is off
- no custom WAF rules are active yet

Planned WAF rules after mobile regression is resolved:
- block `xmlrpc.php`
- managed challenge on `wp-login.php`
- managed challenge for query strings containing `filter_color`

## Product page production-only dependencies

The live product page includes more than child theme code. On `https://sciuuuskids.it/product/dun-dun/` observed on 2026-05-09:

- WooCommerce variation data is embedded inline in `data-product_variations`
- Variation Swatches is active and localizes `woo_variation_swatches_options`
- WooCommerce localizes `wc_add_to_cart_variation_params`
- `sciuuusprodreviews` renders the full inline guest review form on product pages
- `sciuuusprodreviews` also localizes `SciReviewsConfig`
- Cloudflare Turnstile is loaded on the product page via:
  - `https://challenges.cloudflare.com/turnstile/v0/api.js`
  - hidden token field `cf-turnstile-response`
  - widget container `#sci-turnstile-widget`

Important consequence:
- even when the reviews area is later collapsed by theme JS, the review form and Turnstile widget are already in the DOM and on the network path
- this makes Turnstile a production-only product-page dependency, not just a contact-form dependency

## Security-header differences

Observed on 2026-05-09:
- no `Content-Security-Policy` header was present on the live product page response

Interpretation:
- earlier Trusted Types / CSP console errors are consistent with the pre-disable state of Really Simple Security
- those older CSP errors should not be used as the primary explanation for the current live behavior after the plugin was disabled

## Theme-specific notes that affect production debugging

The child theme adds product-page behavior that can create console noise when stricter CSP or Trusted Types are re-enabled:

- `assets/js/custom.js`
  - builds the reviews accordion UI
- `assets/js/size-feedback.js`
  - updates the size guidance block when variations change

These theme scripts were updated on 2026-05-09 to avoid `innerHTML` assignment so future CSP/Trusted Types testing is cleaner.

## Current mobile regression notes

Symptoms reported:
- mobile product pages load HTML quickly
- variation selection feels slow or unresponsive
- navigation taps can feel less responsive on mobile
- desktop does not reproduce the issue

What current evidence does and does not support:

- supported:
  - Turnstile is definitely on the product page critical path
  - Cloudflare proxying is active, but the product HTML remains dynamic
  - product variation data and variation-swatch config are present in the live HTML

- not currently supported:
  - Cloudflare HTML caching corrupting product variation markup
  - Rocket Loader rewriting scripts
  - active CSP headers blocking current live inline config scripts

## Working diagnosis order

1. Turnstile on product review form
   - highest-priority live test after disabling Really Simple Security
   - reason: it is the new Cloudflare-coupled client script present directly on product pages

2. Main-thread pressure from product-page script mix on mobile
   - Variation Swatches
   - WooCommerce variation script
   - Blocksy product/gallery scripts
   - review form JS
   - Turnstile

3. Secondary Cloudflare browser/security features
   - only worth testing after Turnstile is isolated

## Recommended production checks

1. In `Sciuuus Product Reviews` settings, temporarily disable guest-review Turnstile and retest the same mobile product page.
2. If the lag disappears, keep Turnstile off for product reviews temporarily or move to a lazy-load design so it only renders when the reviews panel is opened.
3. If the lag remains, temporarily gray-cloud the product host or use a Cloudflare bypass hostname for a like-for-like mobile comparison.
4. Record one clean test with:
   - mobile browser with extensions disabled
   - preserve log
   - CPU throttle if needed
   - filter on `JS`, `Fetch/XHR`, and long tasks

## Agent notes

When debugging from this theme repo:
- do not assume local Docker reproduces Cloudflare or Turnstile behavior
- do not assume theme-only code is sufficient context for live product-page regressions
- always check whether a product-page issue is actually coming from `sciuuusprodreviews`, Turnstile, or another production-only plugin path
- always call out plugin files that must be deployed separately from `wp-content/themes/blocksy-child`
- after every patch, provide a clear `plugins to push` list when any changed files live under `wp-content/plugins/...`

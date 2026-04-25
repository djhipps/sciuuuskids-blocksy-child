# Claude Code Notes (blocksy-child)

## Work Area
- Primary scope: this theme directory only.
- Keep changes compatible with WooCommerce + Blocksy parent.

## Where To Change What
- Header markup/hooks: `inc/header-custom.php`
- Footer markup/hooks: `inc/footer-custom.php`
- Product PHP hooks: `inc/woocommerce/product-hooks.php`
- Product desktop styles: `assets/css/woocommerce-product.css`
- Product mobile styles: `assets/css/product-page-mobile.css`
- Enqueues and module loading: `functions.php`

## Important Context
- Reviews are not fully theme-native: plugin `sciuuusprodreviews` can render inline review summary/form/list.
- Theme currently hides default Woo product tabs and uses custom details accordion.
- Avoid adding duplicate review form logic in theme if plugin already owns submission flow.

## Validation
- Lint changed PHP files with `php -l`.
- Manually check one product page in desktop + mobile widths.

## Database / WP-CLI Inspection
- A `wordpress-cli` sidecar is defined in the parent `wp-docker/docker-compose.yml` (profile: `cli`, so it does not auto-start).
- Canonical invocation (always include `--no-deps` — omitting it recreates mysql/wordpress):
  `docker compose run --rm --no-deps wordpress-cli wp <args>`
- Uses the official `wordpress:cli` image, shares the `./wordpress` bind mount and `wordpress_network` with the running stack.
- Permission allow-rules for this invocation live in `wp-docker/.claude/settings.json`.
- Useful for: widget state (`wp widget list sidebar-woocommerce`), options, meta, term/attribute inspection — anything the `/wp-admin` UI exposes.

## Browser / Rendered-Page Inspection
Two MCP servers are registered at local scope for the `wp-docker` project tree (entries in `~/.claude.json`). Both only load at session start — a fresh Claude Code session under `wp-docker` picks them up automatically.

**Playwright MCP** (`npx @playwright/mcp@latest`) — isolated headless Chromium.
- On first browser launch Playwright downloads Chromium (~170MB, one-off).
- Useful for: post-JS DOM inspection, wp-admin pages behind auth, responsive screenshots (desktop/mobile widths), verifying the mobile filter toggle on the shop page.
- For wp-admin: log in once via the Playwright browser; reuse the session for subsequent calls.

**Chrome DevTools MCP** (`npx chrome-devtools-mcp@latest`) — attaches to the user's real Chrome over the DevTools Protocol.
- Requires Chrome launched with a remote debugging port (e.g. `--remote-debugging-port=9222`) for the MCP to attach.
- On WSL2 the MCP can reach Chrome running on the Windows host via `localhost` (WSL2 mirrors Windows localhost).
- Useful when the user's actual browser state matters — already-logged-in wp-admin session, real cookies, live DOM as the user sees it.
- Prefer Playwright for "start from scratch and verify" flows; prefer Chrome DevTools for "what is the user seeing right now" flows.

# Sciuuus Kids — WordPress Plugin Inventory

**Last updated:** 2026-05-16
**Source:** wp-admin > Plugins (local Docker stack, post db-refresh)

---

## Active plugins

| Plugin | Version | Author | Notes |
|---|---|---|---|
| All in One SEO | 4.9.7.1 | All in One SEO Team | |
| Blocksy Companion | 2.1.42 | CreativeThemes | Required by Blocksy theme |
| Broken Link Checker by AIOSEO | 1.2.10 | All in One SEO Team | |
| Burst Statistics | 3.4.2 | Burst Statistics | |
| Complianz – Terms and Conditions | 1.3.0 | Complianz | **Deactivate locally after each db-refresh** — live only |
| Complianz \| GDPR/CCPA Cookie Consent | 7.4.6 | Complianz | |
| Cron Logger | 1.3.0 | Palasthotel | |
| Flexible Product Fields | 2.14.4 | WP Desk | |
| Google for WooCommerce | 3.6.2 | WooCommerce | Requires WooCommerce |
| Media Library Organizer | 2.1.1 | Themeisle | |
| Menu Image | 3.13 | Freshlight Lab | |
| Meta for WooCommerce | 3.7.0 | Meta | Requires WooCommerce — **Deactivate locally after each db-refresh** — live only |
| Packlink PRO Shipping | 4.1.1 | Packlink Shipping S.L. | **Deactivate locally after each db-refresh** — live only |
| Payment Plugins for PayPal WooCommerce | 2.0.17 | Payment Plugins | Requires WooCommerce |
| Redirection | 5.7.5 | John Godley | **Deactivate locally after each db-refresh** — live only |
| Sciuuus Admin | 0.1.0 | Sciuuus Kids | Custom — shared settings & integrations |
| Sciuuus AI Contact | 0.1.0 | Sciuuus Kids | Custom — contact form with Turnstile |
| Sciuuus News | 1.1 | Damian Hippisley | Custom |
| Sciuuus Product Reviews | 0.1.0 | Sciuuus Kids | Custom — moderated product reviews |
| Sciuuus Reviews | 1.1 | Damian Hippisley | Custom |
| Sciuuus Size | 1.0 | Damian | Custom — size guide links |
| Site Kit by Google | 1.178.0 | Google | |
| Variation Swatches for WooCommerce | 2.2.3 | Emran Ahmed | Requires WooCommerce 8.0+ |
| WooCommerce | 10.7.0 | Automattic | Cannot deactivate — required by 6 plugins |
| WooCommerce Stripe Gateway | 10.7.0 | Stripe | Requires WooCommerce |
| WooPayments | 10.7.1 | WooCommerce | Requires WooCommerce |
| WP Consent API | 2.0.1 | WordPress Contributors | |
| WP Crontrol | 1.21.0 | John Blackbourn | |
| WP Fastest Cache | 1.4.8 | Emre Vona | |
| WP Mail SMTP | — | WPForms | |

## Inactive plugins (installed, not active)

| Plugin | Version | Author | Notes |
|---|---|---|---|
| Really Simple Security | 9.5.11 | Really Simple Security | Intentionally inactive locally |
| SciuuusMailFilter | 1.4 | Damian | Archived — mail filter now inside sciuuusadmin |
| TranslatePress – Multilingual | 3.1.9 | Cozmoslabs | Local only — not on live |

---

## Post-db-refresh checklist

After running `scripts/db-refresh.sh` in sciuuusadmin, the imported DB re-activates the live plugin set.
Manually deactivate these four in **wp-admin > Plugins** — they have no local files:

- [ ] Complianz – Terms and Conditions
- [ ] Meta for WooCommerce
- [ ] Packlink PRO Shipping
- [ ] Redirection

WP will flag them with a missing-files warning, making them easy to spot.

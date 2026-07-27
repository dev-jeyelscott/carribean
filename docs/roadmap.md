# Coast & Cay — Current Delivery Roadmap

## Current state

The application has moved beyond the original brochure and manual-inquiry
baseline. The active implementation now includes the restaurant catalogue,
session cart, checkout, payments, customer accounts, order tracking, CMS,
Filament administration, and CI foundations.

The retired Reservation Request, Order Inquiry, Banquet Hall, catering, and
private-event workflows must remain removed from runtime code and public copy.

## Remaining delivery priorities

### 1. Scope consistency

- Keep routes, navigation, sitemap, seeders, tests, and documentation aligned.
- Remove legacy Le Jardin and generic fine-dining placeholder copy.
- Keep Contact Inquiry as the only public inquiry workflow.
- Preserve historical migrations that may already have run.

### 2. Public experience

- Complete the Caribbean visual refresh across Home, Menu, About, Gallery,
  Contact, account, cart, checkout, and order pages.
- Keep motion progressive and reduced-motion safe.
- Replace development images and copy when approved client assets arrive.

### 3. Commerce validation

- Verify server-side price recalculation.
- Verify coupons, tax, delivery ZIP codes, and delivery minimums.
- Verify guest and registered checkout.
- Verify Stripe webhook signatures and idempotency.
- Verify cash-payment settings and order transitions.

### 4. Administration

- Verify menu, option, coupon, customer, order, CMS, gallery, and contact
  inquiry resources.
- Verify dashboard metrics and order actions.
- Keep one Filament panel and one administrator access model.

### 5. Content and SEO

- Replace development brand details with approved client content.
- Publish approved legal pages.
- Publish Blog navigation only when an article is public.
- Verify metadata, canonical URLs, sitemap, robots rules, and structured data.

### 6. Quality and launch

- Run Pint, Larastan, Pest, Vite build, and Playwright.
- Complete mobile, tablet, laptop, and desktop acceptance testing.
- Verify queue worker, scheduler, persistent storage, HTTPS, mail, Stripe
  webhook endpoint, and backups.
- Complete administrator handover and production smoke testing.

## Scope cut line

Do not add multiple locations, POS integration, inventory, scheduled ordering,
driver tracking, table reservations, banquet/catering workflows, loyalty,
gift cards, saved addresses, multiple currencies, or native applications
without a separately approved scope change.

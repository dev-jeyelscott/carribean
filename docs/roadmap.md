# Coast & Cay — Detailed Implementation Roadmap

## 1. Project objective

Deliver a production-ready, single-location Caribbean restaurant website with:

* A warm, modern Caribbean visual identity
* Menu catalogue with item options and add-ons
* Session-based shopping cart
* Coupons
* Guest and registered checkout
* Pickup and ZIP-based local delivery
* Stripe-hosted card payments
* Cash at pickup and cash on delivery
* Customer order tracking
* Filament order and content administration
* Reservation and contact workflows
* Blog, FAQ, legal pages, and basic SEO
* Automated tests, CI, deployment documentation, and administrator training

The client requested a restaurant website comparable in breadth to the supplied reference site, with content provided by their consultant, a three-week delivery target, and a budget of **$2,000–$2,500**.

The implementation must follow the locked Option A scope. Anything explicitly excluded from that scope remains a separate change request.

---

# 2. Current repository baseline

The repository is a useful Laravel restaurant-content foundation, not an empty project. It currently has only the initial commit and does not yet expose the agreed `develop` branch.

The installed application stack already includes:

* Laravel 13
* PHP 8.4
* Livewire 4
* Flux UI
* Laravel Fortify
* Filament 5
* Pest
* Larastan
* Laravel Pint
* Laravel Sail

The frontend already uses Tailwind CSS 4, Alpine.js 3, GSAP, and Vite 8. No JavaScript SPA or replacement framework is needed.

The existing public application currently exposes brochure and inquiry routes for Home, Menu, Gallery, Banquet Hall, Reservation Request, Order Inquiry, and Contact.

The existing catalogue should be extended rather than replaced:

* `MenuCategory` already supports slugs, descriptions, visibility, and ordering.
* `MenuItem` already supports category, name, slug, description, price, image, visibility, and ordering.
* `Page` already supports content, structured sections, publication status, and SEO metadata.
* `SiteSetting` already provides cached editable configuration, making it suitable for public restaurant settings.
* Responsive image generation and cleanup already exist.
* Filament already discovers resources, pages, and widgets from the standard application directories.

## Important migration requirement

`MenuItem::price` is currently stored and cast as a decimal string. Commerce calculations will use integer cents, so this must be migrated safely before the cart and checkout become authoritative.

---

# 3. Delivery principles

## Architecture rules

1. Work only on `develop`.
2. Do not create a branch for every phase.
3. Use small, meaningful conventional commits.
4. Keep controllers thin.
5. Use Livewire only where server-driven interaction is valuable.
6. Use standard Blade for mostly static public content.
7. Use Filament for all administration.
8. Use MySQL, database sessions, and the database queue.
9. Store all money in integer cents.
10. Store tax rates as integer basis points to avoid floating-point calculations.
11. Recalculate all prices on the server.
12. Snapshot order names, prices, options, addresses, coupon details, and tax.
13. Use database transactions for order placement and payment-state changes.
14. Dispatch order mail and notifications only after the transaction commits.
15. Do not introduce a repository layer, command bus, event bus, Redis, or microservices.

Laravel’s transaction API automatically rolls back failed transaction closures and supports retry attempts for deadlocks. Queued mail that depends on newly created records should be dispatched after commit.

## Presentation rules

* Temporary development brand: **Coast & Cay**
* Tagline: **Caribbean warmth, California ease.**
* Warm cream background
* Deep tropical green primary
* Ocean blue secondary
* Soft sand surfaces
* Muted coral highlights
* Dark forest text and footer
* Elegant serif headings
* Clean sans-serif body text

Tailwind CSS 4 design tokens should remain CSS-first using `@theme` variables instead of introducing a JavaScript configuration solely for branding.

The existing application already includes GSAP sections and reduced-motion handling. We will refactor the visual language while preserving the accessibility behavior.

---

# 4. Three-week delivery schedule

This is a **15-working-day launch roadmap**.

It is achievable only when:

* Scope remains frozen.
* Client content is delivered during development.
* Stripe and hosting access are not delayed.
* No excluded features are introduced.
* Revision rounds focus on visual and content adjustments, not new systems.

| Day | Primary outcome                                    |
| --: | -------------------------------------------------- |
|   1 | Repository readiness, `develop`, CI baseline       |
|   2 | Caribbean design tokens and public layout          |
|   3 | Homepage, navigation, footer, responsive shell     |
|   4 | Catalogue extensions and item options              |
|   5 | Product details and session cart                   |
|   6 | Pricing, coupons, tax, delivery settings           |
|   7 | Fortify authentication and account shell           |
|   8 | Order database and pickup checkout                 |
|   9 | Delivery checkout, cash methods, order snapshots   |
|  10 | Stripe Checkout and webhook processing             |
|  11 | Order lifecycle, Filament operations, email        |
|  12 | Customer order history and guest order access      |
|  13 | Blog, FAQ, legal content, SEO                      |
|  14 | Full automated and manual acceptance testing       |
|  15 | Production deployment, training, launch validation |

---

# 5. Phase 0 — Repository and quality foundation

**Target:** Day 1

## Objectives

* Create the agreed `develop` branch from the current initial commit.
* Establish a clean WSL and Laravel Sail workflow.
* Add CI before feature implementation.
* Capture the locked scope in repository documentation.
* Ensure existing behavior has a green baseline.

## Primary paths

```text
README.md
.env.example
composer.json
package.json
phpstan.neon
.github/workflows/ci.yml
docs/scope.md
docs/development.md
docs/deployment.md
```

## WSL entry commands

```bash
cd ~/projects/carribean

git fetch origin
git checkout main
git pull --ff-only origin main
git checkout -b develop
git push -u origin develop

./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm ci
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer ci:check
./vendor/bin/sail npm run build
```

## CI pipeline

CI must run:

```bash
composer validate --strict
composer install --no-interaction --prefer-dist --no-progress
npm ci
composer lint:check
composer types:check
php artisan test
npm run build
```

Playwright should be added to CI once its first browser test is introduced.

The repository currently has backend linting, static analysis, and tests, but its frontend scripts only define `dev` and `build`.

## Completion gate

* `develop` exists remotely.
* Sail starts from a clean checkout.
* Database migrations pass.
* Existing tests pass.
* Larastan and Pint pass.
* Production assets build.
* GitHub Actions is green.

## Conventional commit

```text
chore: establish develop workflow and project quality gates
```

---

# 6. Phase 1 — Caribbean public UI refresh

**Target:** Days 2–3

## Objectives

* Replace the current generic fine-dining identity with Coast & Cay placeholders.
* Establish the agreed island-style design system.
* Refactor the global navigation, footer, homepage, and responsive behavior.
* Hide out-of-scope or unconfirmed navigation.
* Preserve existing responsive images and reduced-motion behavior.

## Primary paths

```text
resources/css/app.css
resources/css/theme.css
resources/css/brand-tokens.css
resources/js/app.js
resources/js/public-motion.js
resources/views/components/layouts/public.blade.php
resources/views/components/public/navigation.blade.php
resources/views/components/public/footer.blade.php
resources/views/components/public/homepage-hero.blade.php
resources/views/components/public/menu-card.blade.php
resources/views/components/public/section-heading.blade.php
resources/views/pages/home.blade.php
resources/views/pages/menu.blade.php
app/Http/Controllers/PublicSite/HomeController.php
```

## Homepage implementation

1. Hero
2. Featured categories
3. Restaurant story
4. Featured dishes
5. Cuisine and hospitality section
6. Menu preview
7. Gallery preview
8. Reservation call-to-action
9. Location and operating hours
10. Conditional blog preview
11. Legal and business footer

The current homepage already retrieves published page content, featured menu items, and gallery images, so the data-loading foundation can remain.

## Navigation decisions

Visible:

* Home
* Menu
* About
* Gallery
* Contact
* Reserve a Table
* Order Online
* Cart
* Account

Footer:

* FAQ
* Privacy Policy
* Terms and Conditions
* Refund and Cancellation Policy
* Delivery and Pickup Policy

Hidden:

* Banquet Hall
* Catering
* Blog when there are no published articles

The existing Order Inquiry call-to-action will be replaced by Order Online. The old inquiry records and tables will not be destructively removed during the initial release.

## Manual UI gate

Verify:

* 375px small mobile
* 430px large mobile
* 768px tablet
* 1366px laptop
* 1920px desktop
* Keyboard navigation
* Visible focus states
* Reduced-motion mode
* No animation blocking interaction
* No horizontal overflow

## Conventional commit

```text
feat: refresh public site with caribbean visual system
```

---

# 7. Phase 2 — Orderable catalogue and item options

**Target:** Day 4

## Objectives

* Evolve `MenuItem` into the orderable catalogue.
* Preserve `MenuCategory` as the category model.
* Introduce item-specific option groups and options.
* Create an individual menu-item page.
* Extend Filament management.

## Production-safe price migration

Use an additive rollout:

1. Add nullable `price_cents`.
2. Backfill it from the existing decimal `price`.
3. Update application code to read and write cents.
4. Validate all migrated values with tests.
5. Keep the old `price` column through launch.
6. Remove it only in a later migration after backup and production validation.

No destructive column replacement should happen in the same deployment.

## New and updated fields

### `menu_items`

* `price_cents`
* `image_alt_text`
* `is_featured`
* `is_available`
* `is_purchasable`
* `dietary_labels` JSON, nullable
* `allergen_information`, nullable

### `menu_item_option_groups`

* `menu_item_id`
* `name`
* `is_required`
* `minimum_selections`
* `maximum_selections`
* `sort_order`

### `menu_item_options`

* `menu_item_option_group_id`
* `name`
* `additional_price_cents`
* `is_available`
* `sort_order`

## Primary paths

```text
app/Models/MenuItem.php
app/Models/MenuItemOptionGroup.php
app/Models/MenuItemOption.php
app/Http/Controllers/PublicSite/MenuController.php
app/Http/Controllers/PublicSite/MenuItemController.php
app/Filament/Resources/MenuItems/
app/Filament/Resources/MenuItemOptionGroups/
resources/views/pages/menu.blade.php
resources/views/pages/menu-item.blade.php
routes/web.php
database/migrations/
database/factories/
database/seeders/
tests/Feature/Menu/
tests/Feature/Filament/Menu/
```

## Routes

```text
GET /menu
GET /menu/{menuItem:slug}
```

## Catalogue rules

* Hidden items never appear publicly.
* Visible but unavailable items remain viewable.
* Unavailable items show an availability label.
* Non-purchasable items do not show Add to Cart.
* Required option groups must satisfy minimum and maximum selections.
* Option identifiers and prices are always reloaded from the database.

## Completion gate

* Category and item management works in Filament.
* Product detail pages use slug binding.
* Option selection rules are enforced server-side.
* An unavailable option cannot be submitted manually.
* Prices display from cents without floating-point calculations.

## Conventional commit

```text
feat: extend menu catalogue with purchasable options
```

---

# 8. Phase 3 — Session cart

**Target:** Day 5

## Architecture

Use one server-side cart stored in the Laravel session.

Do not create:

* `carts`
* `cart_items`
* Persistent cart synchronization
* Cart-merging logic
* Saved carts

The same session survives login in the same browser, satisfying the agreed scope.

## Primary paths

```text
app/Support/Cart/SessionCart.php
app/Support/Orders/OrderPriceCalculator.php
app/Livewire/Cart/AddToCart.php
app/Livewire/Cart/CartPage.php
app/Livewire/Cart/CartCount.php
resources/views/livewire/cart/add-to-cart.blade.php
resources/views/livewire/cart/cart-page.blade.php
resources/views/livewire/cart/cart-count.blade.php
resources/views/pages/cart.blade.php
tests/Feature/Cart/
routes/web.php
```

## Cart item state

Each session line stores only identifiers and customer selections:

* Menu item ID
* Selected option IDs
* Quantity

Names and prices may be displayed from a freshly calculated cart view, but the session values never become authoritative pricing.

## Required behavior

* Add item
* Select required options
* Update quantity
* Remove line
* Clear cart
* Show header count
* Show subtotal
* Reject unavailable items
* Reject invalid options
* Merge identical configurations into one line
* Keep differently configured versions as separate lines

Livewire provides server-side form handling and validation without adding a separate client application.

## Completion gate

* Manipulating browser state cannot alter the server-calculated price.
* Required options are enforced.
* Cart survives normal page navigation.
* Logging in does not clear the cart.
* Quantity limits are validated.

## Conventional commit

```text
feat: implement server-side session shopping cart
```

---

# 9. Phase 4 — Pricing, coupons, and fulfillment settings

**Target:** Day 6

## Objectives

* Introduce the authoritative pricing calculator.
* Add one-coupon-per-order support.
* Configure pickup and ZIP-based delivery.
* Add tax and cash-payment settings.
* Add the Accepting Online Orders switch.

## New tables

### `coupons`

* Code
* Type: fixed or percentage
* Discount value
* Minimum subtotal
* Starts at
* Expires at
* Total usage limit
* Usage count
* Active status

### `coupon_usages`

* Coupon ID
* Order ID
* User ID, nullable
* Discount snapshot
* Used timestamp

## Site settings

Extend existing site settings for:

```text
accepting_online_orders
online_orders_closed_message
accepted_delivery_zip_codes
delivery_fee_cents
delivery_minimum_cents
tax_rate_basis_points
cash_at_pickup_enabled
cash_on_delivery_enabled
pickup_instructions
delivery_instructions
```

Payment secrets must remain in environment configuration, never in `site_settings`.

## Calculation order

1. Recalculate merchandise subtotal.
2. Validate and calculate coupon.
3. Calculate discounted taxable amount.
4. Calculate tax.
5. Add delivery fee where applicable.
6. Produce grand total.

## Rules

* Coupon codes are case-insensitive.
* Only one coupon may apply.
* Expired, inactive, or exhausted coupons fail closed.
* Coupon usage is recorded only when an order is placed.
* Delivery requires an accepted ZIP code.
* Delivery minimum uses the post-discount merchandise subtotal.
* Pickup has no delivery fee.
* Checkout is blocked when online orders are disabled.

## Primary paths

```text
app/Models/Coupon.php
app/Models/CouponUsage.php
app/Enums/CouponType.php
app/Support/Orders/OrderPriceCalculator.php
app/Models/SiteSetting.php
app/Filament/Resources/Coupons/
app/Filament/Resources/SiteSettings/
database/migrations/
tests/Feature/Coupons/
tests/Feature/Pricing/
tests/Feature/Fulfillment/
```

## Conventional commit

```text
feat: add coupons and configurable order pricing
```

---

# 10. Phase 5 — Customer authentication and account shell

**Target:** Day 7

## Objectives

* Expose and style Fortify customer authentication.
* Add customer phone numbers.
* Build the account navigation and profile page.
* Preserve restricted Filament access.

The existing `User` model already implements Fortify authentication capabilities and separately restricts Filament access to the configured administrator.

## Customer functionality

* Register
* Login
* Logout
* Forgot password
* Reset password
* Email verification
* Update name
* Update email
* Update phone
* Account overview
* Empty order-history state

Email verification is available but does not block ordering.

## Primary paths

```text
app/Models/User.php
app/Actions/Fortify/CreateNewUser.php
app/Actions/Fortify/UpdateUserProfileInformation.php
app/Providers/FortifyServiceProvider.php
resources/views/auth/
app/Livewire/Account/Profile.php
resources/views/livewire/account/profile.blade.php
resources/views/components/account/navigation.blade.php
routes/web.php
database/migrations/
tests/Feature/Auth/
tests/Feature/Account/
```

## Security gate

* Customers cannot access `/admin`.
* Customers can modify only their own profile.
* Login is rate-limited.
* Password-reset responses do not expose whether an account exists.
* Authentication screens remain keyboard accessible.

## Conventional commit

```text
feat: add customer authentication and account profile
```

---

# 11. Phase 6 — Order domain and checkout

**Target:** Days 8–9

## New tables

### `orders`

Core fields:

* Public UUID or ULID
* Human-readable order number
* User ID, nullable
* Customer name
* Customer email
* Customer phone
* Order status
* Payment status
* Fulfillment method
* Payment method
* Currency
* Subtotal cents
* Discount cents
* Tax cents
* Delivery cents
* Grand total cents
* Tax-rate snapshot
* Coupon snapshot
* Customer note
* Internal note
* Checkout idempotency token
* Placed, paid, cancelled, rejected, picked-up, delivered, and completed timestamps

### `order_addresses`

* Order ID
* Type
* Recipient
* Street
* Apartment or unit
* City
* State
* ZIP code
* Phone
* Delivery instructions

### `order_items`

* Order ID
* Menu item ID, nullable
* Name snapshot
* Description snapshot
* Base unit price cents
* Quantity
* Selected-options JSON snapshot
* Option total cents
* Line total cents

### `order_status_histories`

* Order ID
* Previous status
* New status
* Changed by user ID, nullable
* Public note
* Internal note
* Timestamp

### `payments`

* Order ID
* Provider
* Provider payment ID
* Provider checkout-session ID
* Payment method
* Status
* Amount cents
* Currency
* Paid, failed, and refunded timestamps

## Enums

```text
app/Enums/OrderStatus.php
app/Enums/PaymentStatus.php
app/Enums/FulfillmentMethod.php
app/Enums/PaymentMethod.php
```

## Application classes

```text
app/Actions/Orders/PlaceOrder.php
app/Actions/Orders/TransitionOrderStatus.php
app/Support/Orders/OrderPriceCalculator.php
app/Policies/OrderPolicy.php
```

Only these focused classes are needed. Do not build an abstract command bus or generic workflow engine.

## Checkout flow

1. Load the current session cart.
2. Confirm the restaurant accepts online orders.
3. Validate customer information.
4. Validate pickup or delivery.
5. Validate delivery ZIP and minimum.
6. Reload all menu items and options.
7. Reject unavailable selections.
8. Recalculate all prices.
9. Revalidate the coupon.
10. Create the order inside a transaction.
11. Snapshot items, options, addresses, coupon, and totals.
12. Record initial status history.
13. Create the initial payment record.
14. Route to Stripe or complete the cash checkout.
15. Clear the cart only after order creation succeeds.

## Guest access

Guests receive a temporary signed URL containing the non-sequential public order identifier.

Do not build a public order-number-and-email search form.

## Primary paths

```text
app/Models/Order.php
app/Models/OrderAddress.php
app/Models/OrderItem.php
app/Models/OrderStatusHistory.php
app/Models/Payment.php
app/Enums/
app/Actions/Orders/
app/Policies/OrderPolicy.php
app/Livewire/Checkout/CheckoutPage.php
resources/views/livewire/checkout/checkout-page.blade.php
resources/views/pages/checkout-success.blade.php
resources/views/pages/checkout-cancel.blade.php
database/migrations/
database/factories/
tests/Feature/Checkout/
tests/Feature/Orders/
routes/web.php
```

## Completion gate

* Pickup cash checkout works.
* Delivery cash checkout works.
* Invalid ZIP codes are rejected.
* Totals are recalculated server-side.
* Repeated submission does not create duplicate orders.
* Historical order data remains unchanged after menu edits.
* Failed order creation leaves the cart intact.
* Customers cannot access another customer’s orders.

## Conventional commit

```text
feat: implement transactional checkout and order snapshots
```

---

# 12. Phase 7 — Stripe-hosted Checkout

**Target:** Day 10

## Integration decision

Use the official Stripe PHP SDK with Stripe-hosted Checkout.

Do not introduce:

* Custom card fields
* Raw card handling
* Subscription-oriented architecture
* A custom refund interface
* Multiple payment providers

## Required flow

```text
Order created
→ Stripe Checkout Session created
→ Customer completes hosted payment
→ Signed webhook received
→ Payment marked paid
→ Order remains pending confirmation
→ Confirmation notification queued
```

Stripe requires webhook-driven fulfillment and recommends idempotent processing because the same Checkout Session may be processed more than once or concurrently.

## New table

### `payment_webhook_events`

* Provider
* Provider event ID, unique
* Event type
* Payload JSON
* Received timestamp
* Processed timestamp
* Failed timestamp
* Failure message

## Primary paths

```text
config/services.php
.env.example
app/Services/StripeCheckoutService.php
app/Http/Controllers/StripeCheckoutController.php
app/Http/Controllers/StripeWebhookController.php
app/Actions/Payments/ProcessStripeWebhook.php
app/Models/PaymentWebhookEvent.php
database/migrations/
tests/Feature/Payments/
routes/web.php
```

## Routes

```text
POST /checkout/stripe
GET  /checkout/stripe/success
GET  /checkout/stripe/cancel
POST /webhooks/stripe
```

## Required events

* `checkout.session.completed`
* `checkout.session.async_payment_succeeded`
* `checkout.session.async_payment_failed`
* Refund-related payment events

## Security gate

* Verify the webhook signature.
* Store the provider event ID under a unique database constraint.
* Return success for already processed duplicate events.
* Never mark an order paid from the browser success URL alone.
* Validate currency and expected order amount.
* Never log API secrets or full payment payloads containing unnecessary personal data.

## Local validation

```bash
stripe listen --forward-to http://localhost/webhooks/stripe
stripe trigger checkout.session.completed
```

## Conventional commit

```text
feat: integrate stripe checkout with idempotent webhooks
```

---

# 13. Phase 8 — Order lifecycle, administration, and notifications

**Target:** Day 11

## Status transitions

### Pickup

```text
Pending Confirmation
→ Confirmed
→ Preparing
→ Ready for Pickup
→ Picked Up
→ Completed
```

### Delivery

```text
Pending Confirmation
→ Confirmed
→ Preparing
→ Out for Delivery
→ Delivered
→ Completed
```

### Terminal alternatives

```text
Pending Confirmation
→ Rejected

Pending Confirmation / Confirmed
→ Cancelled
```

All transitions must go through `TransitionOrderStatus`; Filament actions must not update the status column directly.

## Filament resources

```text
app/Filament/Resources/Orders/
app/Filament/Resources/Customers/
app/Filament/Resources/Coupons/
app/Filament/Widgets/OrdersToday.php
app/Filament/Widgets/RevenueToday.php
app/Filament/Widgets/PendingOrders.php
app/Filament/Widgets/RecentOrders.php
app/Filament/Widgets/TopSellingItems.php
```

Filament resources are the framework’s standard CRUD surface, while record actions are appropriate for explicit order transitions requiring confirmation.

## Order actions

* Confirm
* Reject
* Mark preparing
* Mark ready for pickup
* Mark out for delivery
* Mark picked up
* Mark delivered
* Mark completed
* Cancel
* Mark cash payment paid
* Add internal note
* Resend notification

## Queued mail

Customer:

* Order received
* Payment confirmed
* Order confirmed
* Order rejected
* Ready for pickup
* Out for delivery
* Delivered
* Cancelled

Administrator:

* New order
* Payment failure requiring attention
* New reservation request
* New contact inquiry

All order-dependent mail must be queued after commit.

## Automatic completion

Create an hourly scheduled command:

```text
Picked Up for at least one hour → Completed
Delivered for at least one hour → Completed
```

Use `withoutOverlapping()` so multiple scheduler runs cannot process the same batch concurrently.

## Primary paths

```text
app/Actions/Orders/TransitionOrderStatus.php
app/Console/Commands/CompleteFulfilledOrders.php
routes/console.php
app/Mail/Orders/
app/Mail/Inquiries/
app/Filament/Resources/Orders/
app/Filament/Widgets/
tests/Feature/OrderLifecycle/
tests/Feature/Mail/
tests/Feature/Filament/Orders/
```

Filament provides Livewire-based resource testing helpers for records, tables, searches, and actions, so admin behavior should be covered directly rather than only through controller tests.

## Conventional commit

```text
feat: add order fulfillment administration and notifications
```

---

# 14. Phase 9 — Customer and guest order tracking

**Target:** Day 12

## Customer routes

```text
GET /account
GET /account/profile
GET /account/orders
GET /account/orders/{order:public_id}
```

## Guest route

```text
GET /orders/{order:public_id}/guest
```

The guest route must use signed middleware.

## Order page content

* Order number
* Order date
* Current status
* Payment status
* Fulfillment method
* Item snapshots
* Selected options
* Totals
* Delivery or pickup details
* Customer note
* Public status history
* Refresh action
* Confirm received action when allowed

No WebSockets and no real-time polling are included.

Livewire pagination may be used for the customer’s order list while keeping the page number in the URL.

## Primary paths

```text
app/Livewire/Account/OrderList.php
app/Livewire/Account/OrderDetail.php
app/Livewire/Orders/GuestOrderDetail.php
resources/views/livewire/account/order-list.blade.php
resources/views/livewire/account/order-detail.blade.php
resources/views/livewire/orders/guest-order-detail.blade.php
app/Policies/OrderPolicy.php
routes/web.php
tests/Feature/Account/Orders/
tests/Feature/GuestOrders/
```

## Security gate

* Authenticated users can view only their orders.
* Admin access remains in Filament.
* Guest links fail when modified.
* Sequential internal IDs never appear in public URLs.
* Internal staff notes never appear publicly.

## Conventional commit

```text
feat: add secure customer and guest order tracking
```

---

# 15. Phase 10 — CMS, public content, and SEO

**Target:** Day 13

## Blog

Add:

* Draft and published status
* Title
* Slug
* Excerpt
* Body
* Featured image
* Published date
* SEO title
* SEO description

The Blog link appears only when at least one published post exists.

## FAQ

Add a flat sortable FAQ model:

* Question
* Answer
* Visibility
* Sort order

No categories or FAQ search.

## Existing `Page` usage

Use the existing `Page` model for:

* About
* Privacy Policy
* Terms and Conditions
* Refund and Cancellation Policy
* Delivery and Pickup Policy

## Contact and reservation completion

* Add statuses
* Add customer acknowledgement
* Add admin notification
* Add honeypot validation
* Keep route throttling
* Preserve reservation as a request rather than automatic booking

The existing public form routes already run under the `public-forms` throttle middleware.

## SEO baseline

* Page titles
* Meta descriptions
* Canonical links
* Open Graph tags
* Social image
* XML sitemap
* `robots.txt`
* Staging `noindex`
* Restaurant JSON-LD
* Article JSON-LD
* Semantic headings
* Descriptive alt text
* Clean slug URLs

## Primary paths

```text
app/Models/BlogPost.php
app/Models/Faq.php
app/Filament/Resources/BlogPosts/
app/Filament/Resources/Faqs/
app/Http/Controllers/PublicSite/BlogController.php
app/Http/Controllers/PublicSite/FaqController.php
app/Http/Controllers/PublicSite/PageController.php
resources/views/pages/blog/
resources/views/pages/faq.blade.php
resources/views/pages/content-page.blade.php
resources/views/components/seo/
public/robots.txt
routes/web.php
database/migrations/
tests/Feature/Content/
tests/Feature/Seo/
```

## Conventional commit

```text
feat: complete cms pages blog faq and seo metadata
```

---

# 16. Phase 11 — Automated and manual acceptance testing

**Target:** Day 14

## Pest coverage

### Catalogue

* Visible categories appear.
* Hidden categories do not appear.
* Unavailable items cannot be purchased.
* Invalid option combinations fail.
* Decimal-to-cents migration preserves values.

### Cart and pricing

* Add, update, remove, and clear.
* Identical configurations merge.
* Different configurations remain separate.
* Price tampering is ignored.
* Tax rounds correctly.
* Delivery fees and minimums are enforced.

### Coupons

* Valid fixed coupon.
* Valid percentage coupon.
* Expired coupon.
* Inactive coupon.
* Usage-limit exhaustion.
* Minimum-subtotal failure.
* No coupon stacking.

### Checkout

* Guest pickup.
* Guest delivery.
* Registered pickup.
* Registered delivery.
* Cash at pickup.
* Cash on delivery.
* Checkout disabled.
* Duplicate submission.
* Order snapshots remain immutable.

### Payments

* Valid Stripe signature.
* Invalid signature.
* Duplicate event.
* Successful payment.
* Asynchronous payment.
* Payment failure.
* Refund update.
* Incorrect amount or currency.

### Authorization

* Customer order ownership.
* Guest signed links.
* Admin panel access.
* Internal notes hidden.

### Administration

* Order page loads.
* Valid status actions.
* Invalid transitions fail.
* Dashboard widgets display expected totals.

## Playwright Chromium flows

1. Browse menu and open an item.
2. Select required options.
3. Add the item to the cart.
4. Complete guest cash checkout.
5. Register and view order history.
6. Admin confirms and advances the order.
7. Customer refreshes and sees the updated status.

Do not automate Stripe’s external hosted page in every CI run. Cover webhook behavior with feature tests and perform one local/manual Stripe Checkout smoke test.

## Quality commands

```bash
cd ~/projects/carribean

./vendor/bin/sail composer validate --strict
./vendor/bin/sail composer lint:check
./vendor/bin/sail composer types:check
./vendor/bin/sail artisan test
./vendor/bin/sail npm run build
./vendor/bin/sail npx playwright test
```

## Manual acceptance matrix

Verify:

* Chrome
* Edge
* Safari or WebKit
* Small mobile
* Large mobile
* Tablet
* Laptop
* Desktop
* Keyboard-only navigation
* Reduced motion
* Slow network
* JavaScript and network errors
* Failed payment
* Duplicate checkout submission
* Disabled online ordering
* Empty menu, cart, blog, and order states

## Conventional commit

```text
test: cover critical ordering and administration workflows
```

---

# 17. Phase 12 — Production deployment and launch

**Target:** Day 15

## Production requirements

* PHP 8.4
* MySQL
* HTTPS
* Persistent uploaded-file storage
* Queue worker
* Scheduler
* Transactional mail provider
* Stripe webhook endpoint
* Database backups
* Error logging
* Correct application URL
* Secure environment secrets

## Deployment sequence

```bash
cd /var/www/carribean

git fetch origin
git checkout develop
git pull --ff-only origin develop

composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

npm ci
npm run build

php artisan down --retry=60
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan queue:restart
php artisan up
```

The final hosting environment must run:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
php artisan schedule:run
```

The scheduler command is executed by cron every minute; the application itself determines which scheduled jobs are due.

## Production smoke test

1. Homepage loads over HTTPS.
2. Images resolve from persistent storage.
3. Menu displays current production data.
4. Add-to-cart works.
5. Guest cash checkout succeeds.
6. Stripe test or low-value live checkout succeeds.
7. Stripe webhook marks payment paid.
8. Customer email arrives.
9. Administrator email arrives.
10. Admin can confirm an order.
11. Customer order page shows the updated status.
12. Reservation and contact emails arrive.
13. Queue worker processes jobs.
14. Scheduler completes eligible orders.
15. Backups are confirmed.

## Administrator training

Cover:

* Editing restaurant settings
* Uploading images
* Managing categories and menu items
* Managing item options
* Enabling and disabling items
* Enabling and disabling online orders
* Managing coupons
* Reviewing and updating orders
* Recording cash payments
* Publishing pages, FAQs, and blog posts
* Reviewing reservation and contact requests

## Conventional commit

```text
chore: prepare production deployment and operations runbook
```

---

# 18. Client content and configuration deadlines

Development can use placeholders, but the following production deadlines should be enforced.

| Required information                | Needed by | Launch impact                    |
| ----------------------------------- | --------: | -------------------------------- |
| Restaurant name, logo, brand story  |     Day 3 | Final visual review blocked      |
| Menu, prices, item options          |     Day 5 | Checkout acceptance blocked      |
| Address, phone, hours, social links |     Day 6 | Footer and SEO incomplete        |
| Delivery ZIP codes, fee, minimum    |     Day 7 | Delivery checkout blocked        |
| Tax rate approved by accountant     |     Day 7 | Production checkout blocked      |
| Cash-payment decisions              |     Day 7 | Payment methods incomplete       |
| Stripe account and credentials      |     Day 9 | Online payment blocked           |
| Domain and hosting access           |    Day 10 | Deployment blocked               |
| Transactional mail credentials      |    Day 10 | Notifications blocked            |
| Legal policies                      |    Day 12 | Production launch blocked        |
| Final photographs                   |    Day 12 | Final visual acceptance affected |

The application must not infer California tax rules. The configured launch rate remains the client’s or accountant’s responsibility.

---

# 19. Scope cut-line when schedule pressure appears

Security and transactional correctness must never be cut.

## Never defer

* Server-side price recalculation
* Order snapshots
* Checkout transactions
* Stripe signature verification
* Webhook idempotency
* Authorization policies
* Delivery ZIP validation
* Order confirmation
* CI and automated tests
* Legal pages
* Backups

## First items to simplify

1. Decorative GSAP animation depth
2. Gallery transition polish
3. Top-selling dashboard widget
4. Blog homepage preview
5. Advanced admin filters
6. Secondary visual refinements
7. Noncritical responsive-image variants

The underlying Blog feature still ships, but it remains hidden from navigation until content exists.

---

# 20. Definition of done

The project is complete only when:

* `develop` is the active delivery branch.
* CI passes from a clean checkout.
* The public design reflects the approved Caribbean direction.
* Menu items, options, and availability are admin-managed.
* Cart prices are calculated only on the server.
* Coupons, tax, pickup, and delivery work correctly.
* Guest and registered checkout work.
* Stripe and cash payments work.
* Order and payment webhooks are idempotent.
* Order history is immutable.
* Customers can access only their own orders.
* Guest links are signed.
* Filament can manage orders and content.
* Notifications are queued and delivered.
* Blog, FAQ, and legal pages exist.
* Basic SEO metadata and structured data exist.
* Mobile, tablet, and desktop are accepted.
* Production storage, queues, scheduler, HTTPS, and backups work.
* Administrator training is completed.
* Two agreed revision rounds are resolved.
* The 30-day defect-support period begins at written launch acceptance.

---

# 21. Recommended commit sequence

```text
chore: establish develop workflow and project quality gates

feat: refresh public site with caribbean visual system

feat: extend menu catalogue with purchasable options

feat: implement server-side session shopping cart

feat: add coupons and configurable order pricing

feat: add customer authentication and account profile

feat: implement transactional checkout and order snapshots

feat: integrate stripe checkout with idempotent webhooks

feat: add order fulfillment administration and notifications

feat: add secure customer and guest order tracking

feat: complete cms pages blog faq and seo metadata

test: cover critical ordering and administration workflows

chore: prepare production deployment and operations runbook
```

# Final roadmap position

This implementation keeps the strongest parts of the existing repository:

* Laravel and Livewire foundation
* Existing restaurant pages
* Menu categories and items
* Page and site-setting CMS
* Responsive image processing
* Filament administration
* Contact and reservation inquiries
* Pest, Larastan, Pint, Sail, GSAP, and Tailwind

It adds only the systems required for the agreed Option A:

* Orderable menu options
* Session cart
* Pricing and coupons
* Transactional orders
* Guest and registered checkout
* Stripe and cash payments
* Secure order tracking
* Order administration
* Email notifications
* Blog, FAQ, legal pages, SEO, CI, and deployment

It deliberately does **not** become a POS, delivery platform, inventory system, reservation engine, multi-location platform, or generic e-commerce framework.

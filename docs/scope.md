# Locked Option A Scope

## Product

A single-location Caribbean restaurant website with a modern public experience and basic online ordering.

Temporary development identity:

- Name: Coast & Cay
- Tagline: Caribbean warmth, California ease.
- Currency: USD

## Included

- Responsive public website
- Homepage
- Menu categories
- Menu-item listing and details
- Item-specific options and add-ons
- Session-based cart
- One coupon per order
- Guest checkout
- Customer registration and login
- Pickup
- ZIP-code-based local delivery
- Stripe-hosted Checkout
- Cash at pickup
- Cash on delivery
- Customer order history
- Secure guest order links
- Order status tracking
- Filament administration
- Menu and order management
- Contact inquiries
- Reservation requests
- Blog
- FAQ
- Legal pages
- Basic SEO
- Queued email notifications
- Automated tests
- GitHub Actions CI
- Production deployment documentation

## Architecture constraints

- One restaurant
- One currency
- One payment provider
- One Filament admin panel
- One session cart per browser
- One coupon per order
- One delivery fee rule
- MySQL
- Database sessions
- Database queues
- No Redis requirement
- No JavaScript SPA
- No unnecessary repository layer
- No generic command bus or event bus
- No microservices

## Explicitly excluded

- Multiple restaurant locations
- POS integration
- Ingredient inventory
- Recipe costing
- Scheduled ordering
- Driver accounts
- Live delivery tracking
- Distance-based delivery fees
- Kitchen display system
- Reservation capacity engine
- Loyalty points
- Gift cards
- Wishlists
- Product reviews
- Newsletter platform
- Social login
- Saved address book
- Multiple currencies
- Multiple languages
- Advanced coupon targeting
- Refund management inside Filament
- Catering workflow
- Banquet booking workflow
- Native mobile applications

## Scope-change examples

The following require a separate change request:

- Another payment provider
- Another restaurant location
- Scheduled pickup or delivery
- POS integration
- Driver management
- Advanced inventory
- Address-specific tax calculation
- New third-party integrations
- New promotion-rule types

## Pending client configuration

- Final restaurant name
- Logo
- Brand story
- Menu
- Prices
- Item options
- Address
- Delivery ZIP codes
- Delivery fee
- Delivery minimum
- Tax rate
- Operating hours
- Phone and email
- Social links
- Stripe credentials
- Legal content
- Final photographs

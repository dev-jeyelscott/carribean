# Locked Coast & Cay Scope

## Product

A single-location Caribbean restaurant website with online ordering.

Temporary development identity:

- Name: Coast & Cay
- Tagline: Caribbean warmth, California ease.
- Currency: USD

## Included

- Responsive public website
- Homepage
- About page
- Menu categories and item details
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
- Menu, coupon, customer, and order management
- Contact inquiries
- Blog
- FAQ
- Legal pages
- Gallery
- Basic SEO
- Queued email notifications
- Automated tests
- GitHub Actions CI
- Production deployment documentation

## Retired and removed

The following legacy workflows must not be restored:

- Reservation Request
- Order Inquiry
- Banquet Hall
- Catering
- Private-event or private-dining inquiry workflow

Historical migrations may remain for audit and deployment safety. Runtime code,
routes, views, seeders, tests, navigation, sitemap entries, and documentation
must not expose these workflows.

## Architecture constraints

- One restaurant
- One currency
- One payment provider
- One Filament admin panel
- One session cart per browser
- One coupon per order
- One delivery-fee rule
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
- Reservation or table-capacity system
- Banquet or catering workflow
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
- Native mobile applications

## Pending client configuration

- Final restaurant name
- Logo
- Brand story
- Menu and prices
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

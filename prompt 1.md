# Orlina - Full-Featured Premium Ladies E-Commerce Website

Build a fully functional, production-ready, enterprise-level e-commerce platform for women's fashion and luxury lifestyle products using PHP and MySQL.

Brand: Orlina  
Base Currency: BDT (Bangladeshi Taka)  
Target: Bangladesh + International customers  

All prices are stored in BDT in database and converted dynamically for Stripe and other international gateways.

Use high-quality female fashion product images from https://unsplash.com

Copy Right 2026 All rights reserved by Fahmida Islam

--------------------------------------------------------------

## 📁 File Structure

orlina/
├── index.php
├── database_setup.sql
├── migration_stripe_currency.sql
├── includes/
│ ├── db.php
│ ├── functions.php
│ ├── currency.php
│ ├── wishlist.php
│ ├── header.php
│ └── footer.php
├── pages/
│ ├── shop.php
│ ├── product.php
│ ├── cart.php
│ ├── cart_action.php
│ ├── checkout.php
│ ├── stripe_checkout.php
│ ├── stripe_return.php
│ ├── order_success.php
│ ├── order_tracking.php
│ ├── blog.php
│ ├── blog_single.php
│ ├── wishlist.php
│ ├── search.php
│ ├── myaccount.php
│ ├── login.php
│ ├── register.php
│ └── logout.php
├── admin/
│ ├── index.php
│ ├── products.php
│ ├── orders.php
│ ├── users.php
│ ├── blog.php
│ ├── reviews.php
│ ├── settings.php
│ ├── layout.php
│ ├── admin.css
│ └── admin.js
└── assets/
 ├── css/style.css
 ├── js/main.js
 ├── uploads/products/
 └── uploads/avatars/

--------------------------------------------------------------

## 🎨 DESIGN AESTHETIC

White minimal luxury theme.

Primary gradient:
Pink → Purple

Accent:
Soft Yellow highlights

Fonts:
Headings: Playfair Display (700 weight)
Body: Outfit (400-500 weight)

Soft shadows, smooth hover, elegant spacing, premium feminine feel.

CSS Variables:

--primary: linear-gradient(135deg,#ec4899,#8b5cf6);
--accent: #facc15;
--bg: #ffffff;
--surface: #f9f9fb;
--border: rgba(0,0,0,0.06);
--text: #111827;
--text2: #6b7280;
--radius: 14px;

--------------------------------------------------------------

## 🏠 HOME PAGE STRUCTURE

1. Hero Section
Headline: "Luxury, Redefined for Her."
Two CTA buttons:
- Shop Collection
- New Arrivals

2. Feature Strip
- Worldwide Shipping
- Secure Payment
- 7-Day Returns
- Premium Quality
- 24/7 Support

3. Category Grid
- Dresses
- Sarees
- Abayas
- Handbags
- Jewelry
- Shoes
- Skincare

4. Featured Products (8 items)

5. Deal of the Day (highest discount product)

6. New Arrivals (6 newest)

7. Testimonials

8. Blog Preview

9. Newsletter Section

All products fetched dynamically from database.

--------------------------------------------------------------

## 🛍 SHOP PAGE

Features:
- Sidebar filters
- Category filter
- Price range
- Badge filter (NEW/SALE/TRENDING)
- Rating filter
- Sort dropdown
- Pagination
- URL parameters:
?cat=dresses&sort=price_asc&page=2

Dynamic WHERE clause using prepared statements.

--------------------------------------------------------------

## 🛒 CART IMPLEMENTATION (CRITICAL)

- Session-based cart:
$_SESSION['cart'] as [product_id => quantity]

- Remove button MUST use POST form fallback
- JS intercepts form submit for AJAX
- If AJAX fails → normal POST removes item
- Cart badge always present in DOM
- Real-time subtotal update

BASE PATH AUTO-DETECTION:

const _BASE = window.location.pathname.startsWith('/orlina') ? '/orlina' : '';

All AJAX calls must use:
fetch(_BASE + '/pages/cart_action.php')

--------------------------------------------------------------

## 💳 PAYMENT SYSTEM

Supported Methods:

- Cash on Delivery
- bKash
- Nagad
- Stripe (Card)
- SSLCommerz
- PayPal

Stripe Flow:
- stripe_checkout.php creates session
- stripe_return.php verifies payment
- Save stripe_session_id in orders table
- payment_status ENUM('unpaid','paid','refunded')

Stripe uses converted USD amount from BDT.

--------------------------------------------------------------

## 🌍 MULTI-CURRENCY SUPPORT

Stored in settings table:
active_currency

Supported:
BDT (Base)
USD
EUR
GBP

Functions:
getActiveCurrency()
formatPrice()
convertAmount()
getStripeAmount()
getStripeCurrencyCode()

--------------------------------------------------------------

## 👤 USER SYSTEM

Users table:
id, first_name, last_name, email, password, phone, address, city, avatar, role, created_at

role ENUM:
member
admin
super_admin

Features:
- Login
- Register
- Password hashing
- Dashboard
- Order history
- Wishlist
- Change password
- Avatar upload
- Order tracking

--------------------------------------------------------------

## ⭐ REVIEW SYSTEM

reviews table:
id, product_id, user_id, rating, review_text, status, created_at

- 1-5 star rating
- Admin moderation
- Verified buyer badge

--------------------------------------------------------------

## 📝 BLOG SYSTEM

blog_posts:
id, title, slug, content, featured_image, category_id, status, created_at

blog_categories:
id, name, slug

Admin CRUD for blog.

--------------------------------------------------------------

## 🗄 DATABASE STRUCTURE

Tables:

- users
- categories
- products
- product_images
- orders
- order_items
- reviews
- wishlists
- blog_posts
- blog_categories
- settings

products table:
id
category_id
name
slug
description
price (BDT)
old_price
badge
stock
featured
created_at

--------------------------------------------------------------

## 🔐 SECURITY

- Prepared statements (PDO)
- htmlspecialchars on output
- password_hash()
- password_verify()
- CSRF token support
- Session authentication
- Role-based access control

--------------------------------------------------------------

## 🚀 DEPLOYMENT

Localhost folder:
htdocs/orlina/

On VPS root:
Run:
find . -type f -name "*.php" -exec sed -i 's|/orlina/|/|g' {} \;

Or rely on BASE auto-detection.

--------------------------------------------------------------

## 📱 RESPONSIVE

Desktop > 1024px
Tablet 768px - 1024px
Mobile < 768px

Mobile-first layout.
Hamburger navigation.
Stacked grids.

--------------------------------------------------------------

## 🔥 ADMIN PANEL

Dashboard:
- Revenue stats
- Recent orders
- Low stock alert
- User management
- Product CRUD
- Review moderation
- Blog management
- Payment settings
- Currency configuration

Default super_admin:
email: admin@orlina.com
password: Admin@1234 (bcrypt hashed)

--------------------------------------------------------------
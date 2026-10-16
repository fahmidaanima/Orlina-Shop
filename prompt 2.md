# Orlina Admin Dashboard — Full Enterprise Build Prompt

Build a fully functional admin dashboard for the Orlina premium ladies fashion e-commerce platform (PHP/MySQL, BDT base).

The architecture, depth, and database integration must match enterprise-level standards similar to a production SaaS dashboard.

------------------------------------------------------------
📁 FILE STRUCTURE
------------------------------------------------------------

orlina/
├── includes/
│ ├── db.php
│ ├── currency.php
│ └── functions.php
└── admin/
 ├── index.php
 ├── products.php
 ├── orders.php
 ├── users.php
 ├── blog.php
 ├── reviews.php
 ├── settings.php
 ├── layout.php
 ├── footer.php
 ├── admin.css
 ├── admin.js
 └── uploads/

------------------------------------------------------------
🔐 AUTH & ROLE SYSTEM
------------------------------------------------------------

Available roles (users table):
- member
- admin
- super_admin

Role Permissions:

member:
- Access only /orlina/pages/myaccount.php

admin:
- Access admin dashboard
- Manage products
- Manage orders
- Manage reviews
- Cannot delete users
- Cannot promote roles

super_admin:
- Full access
- Change user roles
- Delete users
- Access destructive settings

layout.php must check:

$currentUser = getCurrentUser();
if (!$currentUser || !in_array($currentUser['role'], ['admin','super_admin'])) {
   header("Location: /orlina/pages/login.php");
   exit;
}

------------------------------------------------------------
🎨 DESIGN AESTHETIC (Luxury Light)
------------------------------------------------------------

Theme:
White premium luxury dashboard

CSS Variables:

:root{
 --primary: linear-gradient(135deg,#ec4899,#8b5cf6);
 --accent:#facc15;
 --bg:#f9f9fb;
 --surface:#ffffff;
 --border:rgba(0,0,0,0.06);
 --text-dark:#111827;
 --text-muted:#6b7280;
 --sidebar-w:250px;
 --topbar-h:60px;
}

Fonts:
Headings → Playfair Display
Body → Outfit

Soft shadow:
box-shadow:0 10px 30px rgba(0,0,0,0.05);

------------------------------------------------------------
📊 DASHBOARD (index.php)
------------------------------------------------------------

## Stat Cards (4)

- Total Products (COUNT products)
- Total Orders (COUNT orders)
- Pending Orders badge
- Total Revenue (SUM total_amount WHERE status != 'cancelled')
- Total Users
- New users this month

Query examples:

SELECT COUNT(*) FROM products;
SELECT COUNT(*) FROM orders WHERE status='pending';
SELECT SUM(total_amount) FROM orders WHERE status!='cancelled';

## Recent Orders Table

Columns:
Order #
Customer Name
Total
Payment
Status
Date
View

Query:
SELECT o.*, u.first_name, u.last_name
FROM orders o
LEFT JOIN users u ON o.user_id=u.id
ORDER BY o.created_at DESC
LIMIT 8;

## Top Selling Products Sidebar

Query:
SELECT p.name, p.price,
COUNT(oi.id) AS sold
FROM order_items oi
JOIN products p ON p.id=oi.product_id
GROUP BY oi.product_id
ORDER BY sold DESC
LIMIT 5;

------------------------------------------------------------
🛍 PRODUCTS (products.php)
------------------------------------------------------------

### Features:
- Full CRUD
- Search by name
- Filter by category
- Pagination
- Badge selection (NEW / SALE / TRENDING)
- Featured toggle
- Image upload support

### Add/Edit Modal (multipart/form-data)

Fields:
- Name (auto slug generation)
- Category (dropdown from categories table)
- Price (BDT)
- Old Price
- Stock
- Badge
- Featured
- Description
- Image upload (priority over URL)

Slug generation:

$slug = strtolower(preg_replace('/[^a-z0-9]+/i','-', $name));

Prepared statement required:

$stmt = $conn->prepare("INSERT INTO products
(category_id,name,slug,description,price,old_price,stock,badge,featured)
VALUES (?,?,?,?,?,?,?,?,?)");

### Delete Safety

Before delete:
Check if product exists in order_items table.
If exists → block deletion.

------------------------------------------------------------
📦 ORDERS (orders.php)
------------------------------------------------------------

Features:
- Paginated list
- Filter by status
- Search by order number
- Inline status update (auto-submit)

Order Status Values:
pending
processing
shipped
delivered
cancelled

Payment Status:
unpaid
paid
refunded

Order Detail View:
- Customer info
- Shipping address
- Items list
- Grand total
- Payment method
- Stripe session ID (if exists)

------------------------------------------------------------
👤 USERS (users.php)
------------------------------------------------------------

Features:
- List users
- Search by name/email
- Filter by role
- Add user modal
- Role dropdown (super_admin only)
- Delete user (super_admin only)

Show:
- Total orders count per user
Query:
SELECT COUNT(*) FROM orders WHERE user_id=?

------------------------------------------------------------
📝 BLOG MANAGEMENT
------------------------------------------------------------

Tables:
blog_posts
blog_categories

Features:
- Create post
- Edit
- Delete
- Status (draft/published)
- Featured image upload

------------------------------------------------------------
⭐ REVIEWS (reviews.php)
------------------------------------------------------------

Features:
- Approve
- Reject
- Delete
- Show product name + user name
- Rating badge

------------------------------------------------------------
⚙ SETTINGS (settings.php)
------------------------------------------------------------

### Currency Settings

Stored in settings table:

active_currency

Supported:
BDT (default)
USD
EUR
GBP

### Payment Keys

Stored in settings table:

stripe_publishable_key
stripe_secret_key
paypal_client_id
sslcommerz_store_id
sslcommerz_store_password

All values saved using key/value structure.

------------------------------------------------------------
📂 FILE UPLOAD RULE
------------------------------------------------------------

Uploads stored in:
orlina/admin/uploads/

Absolute path for move_uploaded_file:
__DIR__ . '/uploads/'

Stored image path:
'/orlina/admin/uploads/filename.jpg'

------------------------------------------------------------
🔒 SECURITY RULES
------------------------------------------------------------

- Always use prepared statements
- Escape output with htmlspecialchars()
- Use sanitize()
- Never raw query with user input
- Block destructive actions unless super_admin

------------------------------------------------------------
📱 RESPONSIVE
------------------------------------------------------------

- Sidebar collapses to hamburger
- Tables horizontally scrollable
- Cards stack vertically

------------------------------------------------------------
🧠 INTEGRATION POINTS
------------------------------------------------------------

- Uses getCurrentUser()
- Uses formatPrice()
- Uses generateOrderNumber()
- Currency conversion handled in currency.php
- Stripe checkout handled in:
  /pages/stripe_checkout.php
  /pages/stripe_return.php

------------------------------------------------------------
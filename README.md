# ⌚ Watch Store

A full-featured PHP e-commerce web application for buying and managing premium watches — with a complete admin panel, cart system, order management, coupons, and more.

🌐 **Live Demo:** [https://aeterna-nexus.wuaze.com/website_main/](https://aeterna-nexus.wuaze.com/website_main/)

---

## 🚀 Features

### 🛍️ User Panel
- Browse & search watches by category
- Product detail pages with multiple images
- Add to cart, update quantity, remove items
- Apply coupon codes at checkout
- Secure checkout & order placement
- Order history & order detail view
- User profile with photo upload
- Account registration, login, email activation & password reset

### 🔧 Admin Panel
- Dashboard with overview
- Product management (add, edit, delete, images)
- Category & coupon management
- Order management with detail view
- User management
- Review moderation
- Contact message inbox
- Admin profile management

---

## 🛠️ Tech Stack

| Layer      | Technology          |
|------------|---------------------|
| Backend    | PHP (Procedural)    |
| Database   | MySQL               |
| Frontend   | HTML, CSS, JS       |
| Email      | PHPMailer / SMTP    |
| Server     | Apache (XAMPP/WAMP) |

---

## 🔗 Live URLs

| Panel         | URL                                                                                     |
|---------------|-----------------------------------------------------------------------------------------|
| 🛍️ User Store  | [aeterna-nexus.wuaze.com/website_main/](https://aeterna-nexus.wuaze.com/website_main/) |
| 🔧 Admin Panel | [aeterna-nexus.wuaze.com/website_main/admin/login.php](https://aeterna-nexus.wuaze.com/website_main/admin/login.php) |

---

## 🔐 Demo Credentials

| Role  | URL                                                              | Username               | Password   |
|-------|------------------------------------------------------------------|------------------------|------------|
| User  | [User Panel](https://aeterna-nexus.wuaze.com/website_main/)     | tushar633712@gmail.com | Tushar@123 |
| Admin | [Admin Panel](https://aeterna-nexus.wuaze.com/website_main/admin/login.php) | admin     | Admin@123  |

> ⚠️ Change credentials before deploying to production.

---

## ⚙️ Local Setup Instructions

1. **Clone the repo**
   ```bash
   git clone https://github.com/tusharsolanki7617/watch_store.git
   ```

2. **Move to server root**
   - XAMPP → `htdocs/watch_store`
   - WAMP → `www/watch_store`

3. **Import the database**
   - Open phpMyAdmin
   - Create a database named `watch_store`
   - Import `database/watch_store.sql`

4. **Configure DB connection**
   - Edit `includes/config.php` with your DB credentials

5. **Start your local server** (XAMPP / WAMP / MAMP)

6. **Visit in browser**
   ```
   http://localhost/watch_store/
   ```

---

## 📁 Project Structure

```
watch_store/
│
├── 📄 index.php                  # Homepage
├── 📄 products.php               # Product listing
├── 📄 product-detail.php         # Single product view
├── 📄 cart.php                   # Shopping cart
├── 📄 checkout.php               # Checkout flow
├── 📄 login.php                  # User login
├── 📄 register.php               # Registration
├── 📄 profile.php                # User profile
├── 📄 my-orders.php              # Order history
├── 📄 order-detail.php           # Order detail
├── 📄 order-success.php          # Order success page
├── 📄 contact.php                # Contact page
├── 📄 about.php                  # About page
├── 📄 services.php               # Services page
├── 📄 forgot-password.php        # Forgot password
├── 📄 reset-password.php         # Reset password
├── 📄 activate.php               # Email activation
├── 📄 verify-payment.php         # Payment verification
├── 📄 logout.php                 # Logout
│
├── 🔧 admin/                     # Admin panel
│   ├── index.php                 # Dashboard
│   ├── login.php / logout.php
│   ├── products.php              # Product list
│   ├── add-product.php           # Add product
│   ├── edit-product.php          # Edit product
│   ├── delete-product.php        # Delete product
│   ├── categories.php            # Categories
│   ├── orders.php                # All orders
│   ├── order-detail.php          # Order detail
│   ├── users.php                 # User management
│   ├── reviews.php               # Review moderation
│   ├── coupons.php               # Coupon management
│   ├── contact-messages.php      # Inbox
│   ├── view-message.php          # View message
│   ├── profile.php               # Admin profile
│   ├── includes/                 # header, footer, sidebar
│   └── assets/                   # css/style.css, js/admin.js
│
├── ⚡ ajax/                       # AJAX handlers
│   ├── add-to-cart.php
│   ├── remove-from-cart.php
│   ├── update-cart.php
│   ├── get-cart-count.php
│   ├── apply-coupon.php
│   ├── search.php
│   ├── register.php
│   ├── forgot-password.php
│   ├── submit-review.php
│   └── send_background_email.php
│
├── ⚙️ includes/                   # Core config & helpers
│   ├── config.php                # DB connection
│   ├── functions.php             # Helper functions
│   ├── security.php              # Security utilities
│   ├── header.php / footer.php
│   ├── mailer.php                # Email logic
│   ├── SimpleMailer.php
│   └── PHPMailer/                # PHPMailer library
│       ├── PHPMailer.php
│       ├── SMTP.php
│       └── Exception.php
│
├── 🎨 assets/                     # Frontend assets
│   ├── css/style.css             # Main stylesheet
│   ├── js/main.js                # Main JS
│   ├── js/validation.js          # Form validation
│   └── images/                   # Static images
│
├── 🗄️ database/                   # SQL files
│   ├── watch_store.sql           # data
│
├── 🖼️ uploads/                    # User uploaded files
│   ├── products/                 # Product images
│   ├── profiles/                 # User avatars
│   └── admins/                   # Admin avatars
│
├── 📋 logs/                       # Server logs
├── 📋 cli/send_email.php          # CLI email sender

```

---

## 📄 License

This project is for educational purposes.

---

<p align="center">Tushar Solanki — Aeterna Nexus - Watch-Store</p>


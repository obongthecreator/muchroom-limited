# Muchroom Limited - Gadget Inventory Management System

A comprehensive WordPress plugin for managing gadget inventory, sales, orders, and financial tracking for Muchroom Limited.

## Features

### Core Features
- **Inventory Management** — Track stock with opening/closing values, automated imports and sales deductions
- **Take Order System** — Smart order form with automatic calculations, split payment support, and duplicate prevention
- **Receipt Printing** — 80mm thermal printer compatible receipts with ESC/POS Bluetooth support and browser print fallback
- **Financial Summary** — Daily tracking of total sales, transfers, cash, old cash, and cash left
- **Analytics Dashboard** — Interactive charts and graphs filterable by daily, weekly, monthly, and yearly periods
- **Admin Panel** — Full CRUD for products, categories, and staff users

### Categories
- Projectors, Laptops, Bluetooth Speakers, Phones, Phone Accessories, Laptop Accessories, General Gadgets

### Design Features
- **Glassmorphism UI** — Backdrop blur, subtle transparency, and soft shadows
- **Responsive Layout** — Mobile-first single column expanding to multi-column grids
- **Beam-Animated Pill Buttons** — 1px border beam animation on hover
- **Letter-by-Letter Animation** — Bold display font with 80ms staggered reveal
- **Page Transitions** — Smooth fade/slide transitions (350ms ease-in-out)
- **Loading Animation** — Three-dot fade/scale sequence
- **Breakpoint Typography** — Desktop/tablet/mobile responsive font sizes
- **Device Noodle Background** — Animated connection circles with device icons
- **Bento Layout Dashboard** — KPI cards, charts, and activity feeds in organized grid

### Responsive Navigation
- **Desktop (1024px+)** — Full-width horizontal menu
- **Tablet (768px-1023px)** — Condensed horizontal menu
- **Mobile (below 768px)** — Hamburger menu with slide-out drawer

## Installation

1. Upload the `muchroom-gadget-inventory` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress 'Plugins' menu
3. Visit **Settings → Permalinks** and click "Save Changes" to flush rewrite rules
4. Access the system at `yoursite.com/muchroom/`

## Default Login
- **Username:** `admin`
- **Password:** `admin123`

## Pages
| URL | Description |
|-----|-------------|
| `/muchroom/` | Landing page |
| `/muchroom/login/` | Staff login |
| `/muchroom/home/` | Dashboard with category cards and KPIs |
| `/muchroom/take-order/` | Create new orders |
| `/muchroom/sales/` | Today's sales |
| `/muchroom/inventory/` | Stock inventory with opening/closing values |
| `/muchroom/import/` | Import stock |
| `/muchroom/financial/` | Financial summary |
| `/muchroom/analytics/` | Analytics with charts |
| `/muchroom/admin/` | Admin panel (products, categories, users) |
| `/muchroom/category/{slug}/` | Category product listing |

All pages have corresponding history pages at `{page}/history/`.

## Currency
Default currency is Nigerian Naira (₦) with comma-formatted prices (e.g., ₦1,000, ₦10,000, ₦100,000).

## Technical Details
- WordPress plugin architecture with custom rewrite rules
- Session-based authentication for staff users
- AJAX-powered interactions via `wp_ajax` hooks
- REST API endpoints for data retrieval
- Chart.js for analytics visualization
- Web Bluetooth API for ESC/POS thermal printer integration
- CSS custom properties for theming
- Mobile-first responsive design

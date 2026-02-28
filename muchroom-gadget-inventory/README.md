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

> **No shortcodes or WordPress pages required.** This plugin registers its own custom URL routes automatically. Once installed and activated, all pages below are available immediately — you do **not** need to create any WordPress pages or use shortcodes.

## Default Login
- **Username:** `admin`
- **Password:** `admin123`

## Branches

The system supports multiple branch locations. Only Nsukka is currently active; other branches show a "Coming Soon" page.

| Branch | Slug | Status |
|--------|------|--------|
| Nsukka Branch | `nsukka` | ✅ Active |
| Lagos Branch | `lagos` | 🔜 Coming Soon |
| Enugu Branch | `enugu` | 🔜 Coming Soon |
| Owerri Branch | `owerri` | 🔜 Coming Soon |

## Pages

All page URLs follow the pattern `/muchroom/{branch}/{page}/` where `{branch}` is one of the branch slugs above (e.g. `nsukka`).

| URL | Description |
|-----|-------------|
| `/muchroom/` | Branch selection dashboard |
| `/muchroom/{branch}/` | Branch landing page |
| `/muchroom/{branch}/login/` | Staff login |
| `/muchroom/{branch}/home/` | Dashboard with category cards and KPIs |
| `/muchroom/{branch}/take-order/` | Create new orders |
| `/muchroom/{branch}/sales/` | Today's sales |
| `/muchroom/{branch}/sales/history/` | Sales history |
| `/muchroom/{branch}/inventory/` | Stock inventory with opening/closing values |
| `/muchroom/{branch}/inventory/history/` | Inventory history |
| `/muchroom/{branch}/import/` | Import stock |
| `/muchroom/{branch}/import/history/` | Import history |
| `/muchroom/{branch}/financial/` | Financial summary |
| `/muchroom/{branch}/financial/history/` | Financial history |
| `/muchroom/{branch}/analytics/` | Analytics with charts |
| `/muchroom/{branch}/admin/` | Admin panel (products, categories, users) |
| `/muchroom/{branch}/category/{slug}/` | Category product listing |

### Example URLs (Nsukka branch)

| URL | Description |
|-----|-------------|
| `/muchroom/nsukka/` | Nsukka landing page |
| `/muchroom/nsukka/login/` | Nsukka staff login |
| `/muchroom/nsukka/home/` | Nsukka dashboard |
| `/muchroom/nsukka/take-order/` | Take an order at Nsukka |
| `/muchroom/nsukka/sales/` | Nsukka today's sales |
| `/muchroom/nsukka/inventory/` | Nsukka stock inventory |
| `/muchroom/nsukka/analytics/` | Nsukka analytics |
| `/muchroom/nsukka/admin/` | Nsukka admin panel |

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

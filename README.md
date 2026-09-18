# Heim — Coffee Shop POS & Inventory Management System

A full-stack, enterprise-grade Coffee Shop Point-of-Sale (POS) and Recipe-Driven Inventory Management System built on Laravel 10, MySQL/MariaDB, Blade templates, Tailwind CSS, and Alpine.js / Vanilla JS.

---

## 🌟 Architecture & System Highlights

- **Branded Design**: Clean dark pine green (`#155d49`) & white theme with rounded Heim branding.
- **Recipe-Driven Inventory Deduction**: When an item is sold on POS with custom size & add-ons, the system calculates and automatically deducts the exact raw ingredients from inventory.
- **Stock Movements & Waste Tracking**: Complete logging for Stock-In purchases, Waste/Spoilage write-offs, and Manual adjustments.
- **Supervisor-Authorized Refunds**: Prevents unauthorized cancellations by requiring supervisor/manager/owner password credentials before issuing a refund or restock.
- **Daily Consumption Matrix**: High-speed spreadsheet-like daily consumption view showing opening stock, stock in, sales deduction, waste, and closing stock.
- **4-Tier Role-Based Access Control (RBAC)**: Cashier, Supervisor, Manager, and Owner.
- **Live Stock Alerts & Notifications**: Automatic low-stock warnings when inventory reaches reorder threshold.
- **Audit Logging**: Comprehensive trail of staff activity (logins, refunds, inventory changes, price updates).

---

## 🏗️ System Architecture & Data Flow

```
+-----------------------------------------------------------------------------------+
|                                  USER / CASHIER                                   |
+----------------------------------------+------------------------------------------+
                                         |
                                         v
                         +-------------------------------+
                         |      Heim POS Terminal        |
                         |  (Category filter, Add-ons,   |
                         |   Sizes, Cash/GCash/Card)     |
                         +---------------+---------------+
                                         |
                                         | 1. Checkout / Submit Order
                                         v
                         +-------------------------------+
                         |   OrderController @ store     |
                         |   - Creates Order & Items     |
                         |   - Calculates Tax & Total    |
                         |   - Creates Cash Transaction  |
                         +---------------+---------------+
                                         |
                                         | 2. Recipe Engine Triggered
                                         v
+-----------------------------------------------------------------------------------+
|                        RECIPE-BASED INVENTORY ENGINE                              |
|                                                                                   |
|  For each OrderItem:                                                              |
|   1. Base Product Recipe -> deducts (e.g. 18g Coffee Beans, 150ml Milk, 1 Cup)    |
|   2. Size Multiplier -> scales recipe if Large / Medium                           |
|   3. Selected Add-ons -> deducts extra (e.g. +10g Espresso Beans, +30ml Syrup)   |
|   4. Atomic update on `inventory_items.current_stock`                             |
|   5. Creates `inventory_transactions` record (type: 'sale', ref: Order #)        |
|   6. Checks low-stock threshold -> triggers alert notification if <= min_stock    |
+-----------------------------------------------------------------------------------+
                                         |
                                         v
+-----------------------------------------------------------------------------------+
|                         ANALYTICS & MANAGEMENT ENGINE                             |
|                                                                                   |
|  - Daily Consumption Matrix : [Opening + In - Sold - Waste = Closing]             |
|  - Sales Reports            : Hourly volume, Payment methods, Best sellers        |
|  - Inventory Reports        : Stock valuation, Reorder predictions                |
|  - Audit Log Trail          : Records actor, role, IP, and payload                |
+-----------------------------------------------------------------------------------+
```

---

## 👥 Role Hierarchy & Access Matrix

| Feature / Page | Cashier | Supervisor | Manager | Owner |
| :--- | :---: | :---: | :---: | :---: |
| **POS Terminal & Checkout** | ✅ | ✅ | ✅ | ✅ |
| **View Orders & Reprint Receipts** | ✅ | ✅ | ✅ | ✅ |
| **Supervisor-Authorized Refund** | ❌ *(Needs Sup Auth)* | ✅ | ✅ | ✅ |
| **Product & Category Catalog** | ❌ | ✅ | ✅ | ✅ |
| **Recipe Management (BOM)** | ❌ | ✅ | ✅ | ✅ |
| **Inventory Stock-In & Waste** | ❌ | ✅ | ✅ | ✅ |
| **Daily Consumption Matrix** | ❌ | ❌ | ✅ | ✅ |
| **Sales & Inventory Reports** | ❌ | ❌ | ✅ | ✅ |
| **Audit Logs & Low Stock Alerts** | ❌ | ❌ | ✅ | ✅ |
| **Staff & User Management** | ❌ | ❌ | ❌ | ✅ |

---

## 🛠️ Step-by-Step System Workflows

### 1. POS Terminal & Order Flow
1. **Product Selection**: Cashier browses categories or searches items. Clicking opens the modal to pick Size (Regular, Medium, Large) and optional Add-ons (Extra Shot, Oat Milk, Vanilla Syrup).
2. **Cart & Calculations**: Subtotal, VAT (12%), and discounts (Senior/PWD 20%, Custom %, or Flat Amount) compute live.
3. **Payment**: Supports **Cash** (with live change calculator), **GCash / E-Wallet**, and **Credit / Debit Card** with reference number capture.
4. **Instant Receipt & Stock Deduction**: Receipt prints with the rounded Heim brand logo and complete itemization while inventory is automatically deducted in the background.

### 2. Recipe & Inventory Deduction Flow
- Each Product (or Variant) links to multiple ingredients defined in `recipes` and `recipe_ingredients`.
- When an order completes, `RecipeService` retrieves the recipe, multiplies ingredient quantities by variant multiplier, adds add-on ingredient consumption, and records an `inventory_transactions` row with `type='sale'`.
- If an ingredient drops below its minimum threshold (`min_stock`), a `Notification` is created and badge counters update in real-time.

### 3. Supervisor Refund Flow
- If a customer requests a refund or cancellation on the Orders screen, a Cashier cannot authorize it alone.
- A modal prompts for a **Supervisor / Manager / Owner's email & password**.
- Once verified via `SupervisorVerificationService`, the order status changes to `refunded`, payments are updated, ingredients are restored back to inventory, and an entry is logged in the `AuditLog`.

### 4. Daily Consumption & Inventory Matrix
- Located under **Consumption** (Manager / Owner).
- Displays a date-filtered matrix for all raw ingredients:
  $$\text{Closing Stock} = \text{Opening Stock} + \text{Stock In} - \text{Sales Used} - \text{Waste} \pm \text{Adjustments}$$
- Can be exported or filtered by date range for accurate physical counts and variance checks.

---

## 📄 Complete System & Operations Guide

> [!NOTE]
> The full technical guide, role passwords, system flow diagrams, step-by-step development setup, multi-device deployment manual, and file-by-file purpose directory are compiled in the Microsoft Word document:  
> 📁 **[Heim_POS_System_Guide.docx](file:///c:/Users/user/Documents/PROJECT/Heim_POS_System_Guide.docx)**

---

## 🚀 Setup & Deployment Guide

### Prerequisites

| Software | Download | Purpose |
|---|---|---|
| **XAMPP** | [apachefriends.org](https://www.apachefriends.org/download.html) | PHP 8.1+ & MySQL/MariaDB |
| **Composer** | [getcomposer.org](https://getcomposer.org/download/) | PHP dependency manager |
| **Node.js** (LTS) | [nodejs.org](https://nodejs.org/) | Frontend build tools (Vite, Tailwind) |

### Step-by-Step Installation

```bash
# 1. Navigate to project directory
cd C:\Users\user\Documents\PROJECT

# 2. Install PHP dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Create environment file
copy .env.example .env

# 5. Generate application key
C:\xampp\php\php.exe artisan key:generate

# 6. Create database "coffee_shop_pos" in phpMyAdmin (http://localhost/phpmyadmin)
#    Then run migrations and seed sample data
C:\xampp\php\php.exe artisan migrate:fresh --seed

# 7. Build frontend assets (Tailwind CSS & JavaScript)
npm run build

# 8. Start the development server
C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
```

> **Note:** Before running Step 6, make sure XAMPP's **MySQL** service is running and the `coffee_shop_pos` database has been created in phpMyAdmin.

### 🚀 Daily Operations & Multi-User Hosting Guide

#### ❓ Server Architecture FAQ:
- **Does the Main PC need to stay ON?**  
  **Yes.** Your computer serves as the centralized server hosting the MySQL database and Laravel application. If the PC turns off or enters Sleep mode, other devices will lose connection.
  > 💡 **Tip:** Go to **Windows Settings > Power & Sleep** and set *"When plugged in, PC goes to sleep after"* to **Never** during business hours.

- **Does the Main PC need an active INTERNET connection?**  
  - **Using Cloudflare Tunnel (Remote Access):** **YES.** Both the server PC and devices require internet connection to route traffic through Cloudflare.
  - **Using Local Shop Wi-Fi (LAN):** **NO.** You can operate completely offline! As long as the PC and devices are connected to the same shop Wi-Fi router (even without ISP/data), the POS system works locally.

---

### ⚡ 1-Click Fast Launcher & Shutdown

- **To START the system:**  
  Double-click **`START-HEIM.bat`** (on your Desktop or project root). It verifies MySQL, starts the Laravel backend on port 8000, and starts Cloudflare Tunnel to generate your live link.

- **To STOP the system:**  
  Double-click **`STOP-HEIM.bat`** (on your Desktop or project root). It immediately and cleanly stops both the Laravel server and Cloudflare tunnel processes.

---

### 🌐 Manual Hosting Options

#### Option A: Cloudflare Tunnel (Recommended — Free & Access Anywhere)
Allows cashiers, supervisors, and managers to access the POS from **any device (phones, tablets, PCs) anywhere in the world** with a free, secure HTTPS link — no port forwarding or sign-up needed.

1. **Start the Laravel Server:**
   ```bash
   C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Start the Tunnel:**
   Double-click **`start-tunnel.bat`**, or run:
   ```bash
   "C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --protocol http2 --url http://127.0.0.1:8000
   ```

3. **Access:** Open the printed `https://xxxx.trycloudflare.com` URL on any device.

---

#### Option B: Multi-Device Local Wi-Fi (LAN) — No Internet Required
To run strictly offline within your coffee shop:

1. Connect the PC and all POS tablets to the **same shop Wi-Fi router**.
2. Find the server PC's local IP address:
   ```bash
   ipconfig
   # Look for IPv4 Address (e.g., 10.125.224.120)
   ```
3. Update `.env`:
   ```env
   APP_URL=http://YOUR_IP:8000
   ```
4. Start the server:
   ```bash
   C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
   ```
5. Allow port in Windows Firewall (*run PowerShell as Admin once*):
   ```powershell
   netsh advfirewall firewall add rule name="Heim POS Port 8000" dir=in action=allow protocol=TCP localport=8000
   ```
6. On your devices, open the browser and navigate to:
   ```
   http://YOUR_IP:8000
   ```

### 🔑 Default Login Accounts

| Role | Email | Password |
|---|---|---|
| **Owner** | `owner@coffee.com` | `password` |
| **Manager** | `manager@coffee.com` | `password` |
| **Supervisor** | `supervisor@coffee.com` | `password` |
| **Cashier** | `cashier@coffee.com` | `password` |

> ⚠️ **Change all default passwords immediately after first login!**

### 🔧 Troubleshooting

| Issue | Solution |
|---|---|
| Blank page or 500 error | Run `C:\xampp\php\php.exe artisan cache:clear` and `config:clear` |
| Database connection error | Ensure XAMPP MySQL is running and `coffee_shop_pos` database exists |
| "Could not find driver" | Enable `pdo_mysql` in `C:\xampp\php\php.ini` |
| Can't connect from other devices | Check firewall rule, verify same WiFi, use `--host=0.0.0.0` |
| "419 Page Expired" | Refresh the page (CSRF token timeout) |
| IP address changed | Run `ipconfig`, update `.env` `APP_URL`, restart server |

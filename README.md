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

### Quick Setup Summary
```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup database & seed
C:\xampp\php\php.exe artisan migrate:fresh --seed

# 3. Build assets
npm run build

# 4. Start server
C:\xampp\php\php.exe artisan serve
```

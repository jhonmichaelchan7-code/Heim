# ☕ Heim — Coffee Shop POS & Inventory Management System

Welcome to **Heim POS**! An enterprise-grade, touch-friendly Point of Sale (POS), recipe BOM inventory system, and multi-branch management platform designed specifically for specialty coffee shops and cafes.

Engineered with **Laravel 10**, **Standalone SQLite** (no XAMPP or external database server needed), **Tailwind CSS**, and **Alpine.js**, with built-in **Cloudflare Tunneling** for secure, zero-port-forwarding remote connectivity across registers, tablets, and mobile devices.

---

## 🌟 Key Architecture & Features (System Analysis Paper Alignment)

### 1. 🏢 Multi-Branch Support (Bangkal & San Rafael)
* Pre-configured multi-branch operation supporting both the **Bangkal Branch (`BNG-01`)** and **San Rafael Branch (`SRF-02`)**.
* Cashiers and registers operate under assigned branches; receipts automatically imprint the active branch header and address.
* Management dashboard and sales reports provide single-click branch filtering and side-by-side revenue comparisons.

### 2. 🛵 Omnichannel Fulfillment (Dine-In, Takeout, Grab Delivery)
* Integrated channel selection on checkout: **🍽️ Dine-In**, **🛍️ Takeout**, and **🛵 Grab Delivery**.
* Distinct channel badges and thermal slip annotations for kitchen preparation.
* Management analytics breakdown showing sales volume and percentage share by channel.

### 3. 🛡️ Role-Based Security & Manager/Owner Authorization
* **Manager or Owner Credentials Required**: Authorizations for refunds, order cancellations, and order voids strictly require the live email and password credentials of an active Manager or Owner.
* **Strict Cash-Only Refund Policy**: Digital and online payments (GCash, PayMaya, Card) are strictly designated **non-refundable in cash** to preserve external gateway reconciliation and prevent drawer discrepancies.
* **Optional Inventory Restoration**: When processing a return or cancellation, supervisors can choose whether to restore deducted ingredients back to the inventory BOM.

### 4. 🧮 Dynamic Bill of Materials (BOM) & Modifier Tracking
* Finished drinks automatically deduct exact raw ingredients (coffee beans in grams, fresh milk in ml, syrups in ml, cups, lids, and straws).
* **Add-On Modifier Deductions**: Custom add-ons (extra espresso shot, flavored syrups, alternative milk) automatically trigger real-time BOM deductions for their respective raw inventory ingredients.

### 5. 💳 Split & Partial Tender Settlements
* Support for split tender transactions: split a single order across Cash and Digital/Online Payment.
* On-shift cashiers can record subsequent partial payments against their assigned orders until balance is fully settled.
* Mandatory gateway transaction reference numbers for digital/online audits.

### 6. 📊 Peak Rush Hours & Management Analytics
* Hourly traffic distribution analytics (6:00 AM to 10:00 PM) identifying rush peak ordering hours.
* Cashier shift attribution and float drawer variance tracking (Cash Over / Short / Balanced).
* One-click complete CSV/Excel export and automated Google Sheet sync.

### 7. 🧾 Dual Thermal Slip Printing (80mm & 58mm)
* Loyverse-compatible thermal receipt layout with instant toggle between standard 80mm and compact 58mm roll widths.
* Automatic branch metadata, order channel, cashier name, item modifiers, discounts, and BIR VAT breakdown.

---

## 👥 Default Login Accounts

The system comes pre-seeded with 4 default staff roles (Password: `password` for all):

| Role | Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **👑 Owner** | `owner@coffee.com` | `password` | Full system access, multi-branch control, authorizations, reports |
| **💼 Manager** | `manager@coffee.com` | `password` | Authorize refunds/voids, recipe BOMs, inventory stock-ins, analytics |
| **🛡️ Supervisor** | `supervisor@coffee.com` | `password` | POS checkout, inventory adjustments, shift float monitoring |
| **☕ Cashier** | `cashier@coffee.com` | `password` | POS terminal operations, taking orders, split payments, receipts |

---

## ⚡ 1-Click Turnkey Startup (No XAMPP Required!)

Because Heim uses standalone SQLite and Cloudflare Tunneling, **you do not need XAMPP, MySQL, or port forwarding**:

* **To START:** Double-click **[`START-HEIM.bat`](file:///c:/Users/user/Documents/PROJECT/coffee%20shop/START-HEIM.bat)**.  
  1. Automatically prepares the self-contained SQLite database (`database/database.sqlite`).
  2. Launches the local Laravel POS server on `http://127.0.0.1:8000`.
  3. Launches the Cloudflare tunnel, generating a live, secure HTTPS link (`https://*.trycloudflare.com`) accessible from any tablet or phone with zero router configuration!
* **To STOP:** Double-click **[`STOP-HEIM.bat`](file:///c:/Users/user/Documents/PROJECT/coffee%20shop/STOP-HEIM.bat)**.  
  Cleanly terminates the server and background tunnel processes.
* **To RESET FOR DEMO:** Double-click **[`RESET-FRESH-DEMO-DATABASE.bat`](file:///c:/Users/user/Documents/PROJECT/coffee%20shop/RESET-FRESH-DEMO-DATABASE.bat)**.  
  Instantly resets to a fresh demo state with Bangkal & San Rafael branches, recipes, ingredients, and demo accounts.

---

## 🌐 Connecting Devices & Register Terminals

### Method 1: Cloudflare Secure Tunnel (Recommended)
1. Double-click `START-HEIM.bat`.
2. Look for the public tunnel address in the terminal window:
   ```text
   https://xxxx.trycloudflare.com
   ```
3. Open this link on any iPad, Android tablet, phone, or laptop browser inside or outside the store.

### Method 2: Offline Local Shop Network (LAN / Wi-Fi)
1. Ensure all register tablets and the server PC are on the same Wi-Fi.
2. Open Command Prompt and check your PC's IPv4 address (`ipconfig`, e.g., `192.168.1.50`).
3. Open the browser on your tablet:
   ```text
   http://192.168.1.50:8000
   ```
   *(Works 100% offline even during internet provider outages!)*

---

## 📁 Key File Structure
* `database/database.sqlite`: Standalone SQLite database.
* `app/Http/Controllers/PosController.php`: Order creation, branch selection, channel routing, and split payment tender.
* `app/Http/Controllers/OrderController.php`: Order management, partial payment recording, and Manager/Owner credential refund authorization.
* `app/Http/Controllers/DashboardController.php`: Branch filtering, shift metrics, channel stats, and payment breakdown.
* `app/Http/Controllers/ReportController.php`: Branch comparison, rush hour peak traffic distribution, and CSV export.
* `app/Services/InventoryService.php`: Automatic Recipe BOM and Modifier ingredient deduction & return logic.
* `resources/views/pos/index.blade.php`: Real-time touch POS interface with branch/channel toggles and thermal printing.
* `START-HEIM.bat`: Turnkey 1-click launcher for SQLite + Cloudflare tunnel.

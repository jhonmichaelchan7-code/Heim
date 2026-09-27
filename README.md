# ☕ Heim — Coffee Shop POS & Inventory Management System

Welcome to **Heim POS**! A fast, clean, and modern Point of Sale (POS) and inventory management system designed specifically for coffee shops and cafes. Built with Laravel 10, MySQL, Tailwind CSS, and Alpine.js.

---

## 🌟 What Heim POS Does

* **Point of Sale (POS)**: Fast, touch-friendly cashier checkout supporting sizes (12oz, 16oz, 22oz), customizable add-ons (extra shot, syrups, milk alternatives), discounts (Senior/PWD, custom discounts), streamlined payment options (Cash, Online Payment via GCash/Maya/QRPh), and Loyverse-compatible thermal receipt printing (80mm & 58mm).
* **Automatic Recipe Inventory Deduction**: When a drink is sold, the exact ingredients (coffee beans, milk, syrups, cups, lids, and straws) are automatically deducted from your inventory in real-time.
* **Waste & Restock Tracking**: Log newly purchased stocks (Stock In), record damaged or expired items (Waste/Spoilage), and track all movements.
* **Supervisor-Protected Refunds**: Cashiers cannot cancel or refund an order without a supervisor or manager entering their password.
* **Daily Consumption Matrix**: An end-of-day summary showing:  
  $$\text{Closing Stock} = \text{Opening Stock} + \text{Stock In} - \text{Sales Used} - \text{Waste} \pm \text{Adjustments}$$
* **Low Stock Alerts**: Instant notifications when ingredients are running low so you never run out during a morning rush.

---

## 👥 Default Login Accounts

The system comes with 4 pre-configured roles out of the box:

| Role | Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **👑 Owner** | `owner@coffee.com` | `password` | Full system access, staff management, financial reports |
| **💼 Manager** | `manager@coffee.com` | `password` | Inventory control, recipes, daily consumption, sales reports |
| **🛡️ Supervisor** | `supervisor@coffee.com` | `password` | POS checkout, inventory adjustments, authorizing refunds |
| **☕ Cashier** | `cashier@coffee.com` | `password` | POS terminal, taking orders, reprinting receipts |

> [!WARNING]
> For security, please change these default passwords before using the system in an active store!

---

## ⚡ 1-Click Quick Start & Shutdown

If you are on the main server PC, you can start or stop the entire system in one click:

* **To START:** Double-click **[`START-HEIM.bat`](file:///c:/Users/user/Documents/PROJECT/START-HEIM.bat)**.  
  It checks MySQL, boots up the Laravel server on port 8000, and starts the Cloudflare tunnel with your live shareable link.
  *(Keep the opened windows open while using the system!)*
* **To STOP:** Double-click **[`STOP-HEIM.bat`](file:///c:/Users/user/Documents/PROJECT/STOP-HEIM.bat)**.  
  Safely shuts down the background server and tunnel with zero leftover processes.

---

## 🌐 How to Connect Multiple Devices (2 Methods)

You can run Heim POS on multiple phones, tablets, or cashier stations using either of these two methods depending on your shop's setup:

### Method 1: Cloudflare Public Tunnel (Online — Access From Anywhere)

**Why use this?**
* **Access anywhere**: Cashiers and tablets can connect from anywhere inside the shop or even from home using cellular data / mobile hotspots.
* **No router setup**: Works immediately without port forwarding or changing Wi-Fi settings.
* **Secure**: Free automatic HTTPS (`https://`) with Cloudflare security.

**Manual Setup Steps:**
1. **Make sure MySQL is running** in XAMPP.
2. **Start the Laravel server** (in Command Prompt):
   ```cmd
   cd C:\Users\user\Documents\PROJECT
   C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
   ```
3. **Start Cloudflare Tunnel** (in a second Command Prompt):
   ```cmd
   "C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --protocol http2 --url http://127.0.0.1:8000
   ```
   *(Or simply double-click `start-tunnel.bat`)*
4. **Open the Link**: In the terminal, look for the generated link:
   ```text
   https://your-temporary-name.trycloudflare.com
   ```
   Open that link on any phone, iPad, or computer browser!

---

### Method 2: Local Shop Wi-Fi / LAN (Offline — No Internet Needed)

**Why use this?**
* **100% Offline**: Works completely without internet! Even if your internet provider has a service interruption or blackout, your cashiers can still take orders.
* **Private & Fast**: Data stays inside your shop's Wi-Fi router.

**Manual Setup Steps:**
1. Connect your server PC and all tablets to the **same shop Wi-Fi network**.
2. Find your PC's local IP address:
   * Open Command Prompt, type `ipconfig`, and look for your **IPv4 Address** (for example, `192.168.1.17`).
3. Allow port 8000 through Windows Firewall (*only need to do this once*):
   * Open PowerShell as Administrator and run:
     ```powershell
     netsh advfirewall firewall add rule name="Heim POS Port 8000" dir=in action=allow protocol=TCP localport=8000
     ```
4. Start the Laravel server:
   ```cmd
   cd C:\Users\user\Documents\PROJECT
   C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
   ```
5. **Open on any device:** Open the browser on your tablet or phone and go to:
   ```text
   http://192.168.1.17:8000
   ```
   *(Replace `192.168.1.17` with your actual IPv4 address from step 2)*

---

## 🛠️ Initial Installation (New PC Setup Only)

If you are setting up the project on a fresh computer for the first time:

1. **Prerequisites**: Install **XAMPP** (PHP 8.1+ & MySQL), **Composer**, and **Node.js LTS**.
2. **Install PHP packages**:
   ```cmd
   composer install
   ```
3. **Install & build frontend assets**:
   ```cmd
   npm install
   npm run build
   ```
4. **Configure `.env`**:
   ```cmd
   copy .env.example .env
   C:\xampp\php\php.exe artisan key:generate
   ```
5. **Create Database & Seed Data**:
   * Open phpMyAdmin (`http://localhost/phpmyadmin`) and create a database named `coffee_shop_pos`.
   * Run migrations and populate sample products, sizes, recipes, and users:
     ```cmd
     C:\xampp\php\php.exe artisan migrate:fresh --seed
     ```

---

## 💡 Common Questions & Tips

* **Why didn't I need to run `npm run build` when starting manually?**  
  `npm run build` compiles your stylesheets and scripts into [`public/build/`](file:///c:/Users/user/Documents/PROJECT/public/build). Once built, they remain saved on your computer permanently. You only ever need to re-run `npm run build` if you modify frontend source code or templates.
* **Does the main PC need to stay awake?**  
  Yes! The main computer acts as your local server. Make sure to set Windows sleep settings to "Never sleep" when plugged in during shop operating hours.
* **Do I need router port forwarding?**  
  No! You never need port forwarding. Cloudflare Tunnel securely handles external connections, and Local LAN handles offline internal connections.

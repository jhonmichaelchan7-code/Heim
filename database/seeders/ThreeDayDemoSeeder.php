<?php

namespace Database\Seeders;

use App\Models\AddOn;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddOn;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Refund;
use App\Models\Shift;
use App\Models\ShiftCashMovement;
use App\Models\Size;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThreeDayDemoSeeder extends Seeder
{
    protected Carbon $now;
    protected User $owner;
    protected User $manager;
    protected User $supervisor;
    protected User $cashier;

    // Track order sequence counter per day
    protected array $orderSeq = [];

    public function run(): void
    {
        $this->command->info("Starting Three-Day Demo Seeder (2026-09-30 to 2026-10-02)...");

        // 1. Resolve Users
        $this->owner = User::where('email', 'owner@coffee.com')->first()
            ?? User::firstOrCreate(['email' => 'owner@coffee.com'], ['name' => 'Owner Admin', 'password' => bcrypt('password'), 'role' => 'owner']);
        $this->manager = User::where('email', 'manager@coffee.com')->first()
            ?? User::firstOrCreate(['email' => 'manager@coffee.com'], ['name' => 'Maria Santos', 'password' => bcrypt('password'), 'role' => 'manager']);
        $this->supervisor = User::where('email', 'supervisor@coffee.com')->first()
            ?? User::firstOrCreate(['email' => 'supervisor@coffee.com'], ['name' => 'Carlos Reyes', 'password' => bcrypt('password'), 'role' => 'manager']);
        $this->cashier = User::where('email', 'cashier@coffee.com')->first()
            ?? User::firstOrCreate(['email' => 'cashier@coffee.com'], ['name' => 'anna', 'password' => bcrypt('password'), 'role' => 'cashier']);

        // 2. Safe Idempotent Reset of Previous Demo Transactions
        $this->cleanPreviousDemoData();

        // 3. Ensure Full Recipe Coverage for All Products & Sizes
        $this->ensureFullRecipeCoverage();

        // 4. Run Day 1: 2026-09-30 (Wednesday)
        $this->seedDayOne();

        // 5. Run Day 2: 2026-10-01 (Thursday)
        $this->seedDayTwo();

        // 6. Run Day 3: 2026-10-02 (Friday - In Progress up to 11:30 AM)
        $this->seedDayThree();

        // 7. Verify Alerts & Notifications
        $this->generateFinalAlerts();

        $this->command->info("Three-Day Demo Seeding Completed Successfully!");
    }

    /**
     * Clean only transactional/demo data and reset ingredients to baseline stock
     */
    protected function cleanPreviousDemoData(): void
    {
        $this->command->info("Cleaning previous transactional records and resetting stock to baseline...");

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderItemAddOn::truncate();
        OrderItem::truncate();
        Payment::truncate();
        Refund::truncate();
        Order::truncate();
        ShiftCashMovement::truncate();
        Shift::truncate();
        InventoryTransaction::truncate();
        SystemNotification::truncate();
        AuditLog::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Reset ingredients to initial standard stock
        $initialStocks = [
            'Coffee Beans'     => ['current' => 5000, 'min' => 500],
            'Fresh Milk'       => ['current' => 10000, 'min' => 2000],
            'Condensed Milk'   => ['current' => 5000, 'min' => 1000],
            'Chocolate Powder' => ['current' => 3000, 'min' => 500],
            'Matcha Powder'    => ['current' => 1000, 'min' => 200],
            'Caramel Syrup'    => ['current' => 2000, 'min' => 500],
            'Vanilla Syrup'    => ['current' => 2000, 'min' => 500],
            'Hazelnut Syrup'   => ['current' => 2000, 'min' => 500],
            'Ice'              => ['current' => 20000, 'min' => 5000],
            'Water'            => ['current' => 50000, 'min' => 10000],
            'Whipped Cream'    => ['current' => 2000, 'min' => 400],
            'Sugar'            => ['current' => 5000, 'min' => 1000],
            '12oz Cup'         => ['current' => 200, 'min' => 50],
            '16oz Cup'         => ['current' => 200, 'min' => 50],
            '22oz Cup'         => ['current' => 200, 'min' => 50],
            'Lid'              => ['current' => 500, 'min' => 100],
            'Straw'            => ['current' => 500, 'min' => 100],
        ];

        foreach ($initialStocks as $name => $vals) {
            Ingredient::where('name', $name)->update([
                'current_stock' => $vals['current'],
                'minimum_stock' => $vals['min'],
            ]);
        }
    }

    /**
     * Ensure complete BOM recipe ingredients exist for all catalog drinks
     */
    protected function ensureFullRecipeCoverage(): void
    {
        $ingredients = Ingredient::all()->keyBy('name');
        $sizes = Size::all()->keyBy('name');
        $products = Product::with('sizes')->get()->keyBy('name');

        $cupMap = [
            '12oz' => $ingredients->get('12oz Cup')?->id,
            '16oz' => $ingredients->get('16oz Cup')?->id,
            '22oz' => $ingredients->get('22oz Cup')?->id,
        ];
        $lidId = $ingredients->get('Lid')?->id;
        $strawId = $ingredients->get('Straw')?->id;

        // Base recipe profiles
        $profiles = [
            'Americano' => [
                '12oz' => ['Coffee Beans' => 14, 'Water' => 250],
                '16oz' => ['Coffee Beans' => 18, 'Water' => 350],
                '22oz' => ['Coffee Beans' => 22, 'Water' => 450],
            ],
            'Cappuccino' => [
                '12oz' => ['Coffee Beans' => 14, 'Fresh Milk' => 140],
                '16oz' => ['Coffee Beans' => 18, 'Fresh Milk' => 180],
                '22oz' => ['Coffee Beans' => 22, 'Fresh Milk' => 240],
            ],
            'Café Latte' => [
                '12oz' => ['Coffee Beans' => 14, 'Fresh Milk' => 150],
                '16oz' => ['Coffee Beans' => 18, 'Fresh Milk' => 200],
                '22oz' => ['Coffee Beans' => 22, 'Fresh Milk' => 280],
            ],
            'Mocha' => [
                '12oz' => ['Coffee Beans' => 14, 'Fresh Milk' => 140, 'Chocolate Powder' => 15],
                '16oz' => ['Coffee Beans' => 18, 'Fresh Milk' => 180, 'Chocolate Powder' => 20],
                '22oz' => ['Coffee Beans' => 22, 'Fresh Milk' => 240, 'Chocolate Powder' => 25],
            ],
            'Iced Americano' => [
                '16oz' => ['Coffee Beans' => 18, 'Water' => 250, 'Ice' => 150],
                '22oz' => ['Coffee Beans' => 22, 'Water' => 320, 'Ice' => 200],
            ],
            'Iced Latte' => [
                '16oz' => ['Coffee Beans' => 18, 'Fresh Milk' => 200, 'Ice' => 150],
                '22oz' => ['Coffee Beans' => 22, 'Fresh Milk' => 300, 'Ice' => 200],
            ],
            'Iced Mocha' => [
                '16oz' => ['Coffee Beans' => 18, 'Fresh Milk' => 180, 'Chocolate Powder' => 20, 'Ice' => 150],
                '22oz' => ['Coffee Beans' => 22, 'Fresh Milk' => 260, 'Chocolate Powder' => 25, 'Ice' => 200],
            ],
            'Spanish Latte' => [
                '16oz' => ['Coffee Beans' => 18, 'Fresh Milk' => 150, 'Condensed Milk' => 40, 'Ice' => 150],
                '22oz' => ['Coffee Beans' => 22, 'Fresh Milk' => 220, 'Condensed Milk' => 55, 'Ice' => 200],
            ],
            'Caramel Frappe' => [
                '16oz' => ['Coffee Beans' => 14, 'Fresh Milk' => 150, 'Caramel Syrup' => 30, 'Whipped Cream' => 25, 'Ice' => 180],
                '22oz' => ['Coffee Beans' => 18, 'Fresh Milk' => 220, 'Caramel Syrup' => 45, 'Whipped Cream' => 35, 'Ice' => 220],
            ],
            'Mocha Frappe' => [
                '16oz' => ['Coffee Beans' => 14, 'Fresh Milk' => 150, 'Chocolate Powder' => 25, 'Whipped Cream' => 25, 'Ice' => 180],
                '22oz' => ['Coffee Beans' => 18, 'Fresh Milk' => 220, 'Chocolate Powder' => 35, 'Whipped Cream' => 35, 'Ice' => 220],
            ],
            'Matcha Latte' => [
                '16oz' => ['Matcha Powder' => 15, 'Fresh Milk' => 250, 'Ice' => 150],
                '22oz' => ['Matcha Powder' => 20, 'Fresh Milk' => 320, 'Ice' => 200],
            ],
            'Chocolate Latte' => [
                '16oz' => ['Chocolate Powder' => 30, 'Fresh Milk' => 250, 'Ice' => 150],
                '22oz' => ['Chocolate Powder' => 40, 'Fresh Milk' => 320, 'Ice' => 200],
            ],
        ];

        foreach ($profiles as $prodName => $sizeRecipes) {
            $product = $products->get($prodName);
            if (!$product) continue;

            foreach ($sizeRecipes as $sizeName => $ingList) {
                $size = $sizes->get($sizeName);
                if (!$size) continue;

                $recipe = Recipe::firstOrCreate([
                    'product_id' => $product->id,
                    'size_id' => $size->id,
                ]);

                // Ensure cup and lid
                if (isset($cupMap[$sizeName]) && $cupMap[$sizeName]) {
                    RecipeIngredient::firstOrCreate([
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $cupMap[$sizeName],
                    ], ['quantity' => 1]);
                }
                if ($lidId) {
                    RecipeIngredient::firstOrCreate([
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $lidId,
                    ], ['quantity' => 1]);
                }
                // Straw for cold drinks
                if (str_contains(strtolower($prodName), 'iced') || str_contains(strtolower($prodName), 'frappe') || str_contains(strtolower($prodName), 'matcha') || str_contains(strtolower($prodName), 'spanish')) {
                    if ($strawId) {
                        RecipeIngredient::firstOrCreate([
                            'recipe_id' => $recipe->id,
                            'ingredient_id' => $strawId,
                        ], ['quantity' => 1]);
                    }
                }

                // Add ingredients
                foreach ($ingList as $ingName => $qty) {
                    $ing = $ingredients->get($ingName);
                    if ($ing) {
                        RecipeIngredient::firstOrCreate([
                            'recipe_id' => $recipe->id,
                            'ingredient_id' => $ing->id,
                        ], ['quantity' => $qty]);
                    }
                }
            }
        }
    }

    /**
     * DAY 1: 2026-09-30 (Wednesday)
     */
    protected function seedDayOne(): void
    {
        $date = '2026-09-30';
        $this->command->info("Seeding Day 1: {$date}...");

        // Open Shift at 06:45 AM
        $shift = Shift::create([
            'opened_by' => 'anna',
            'opened_at' => Carbon::parse("{$date} 06:45:00", 'Asia/Manila'),
            'starting_cash' => 2000.00,
            'cash_in' => 0,
            'cash_out' => 0,
            'expected_cash' => 2000.00,
            'status' => 'open',
            'authorized_by' => $this->supervisor->id,
            'created_at' => Carbon::parse("{$date} 06:45:00", 'Asia/Manila'),
            'updated_at' => Carbon::parse("{$date} 06:45:00", 'Asia/Manila'),
        ]);

        // Delivery 1 (07:30 AM): Fresh Milk +15,000ml, Coffee Beans +5,000g
        $this->recordStockIn('Fresh Milk', 15000, 'Davao Dairy Farm', 'Morning fresh dairy delivery', "{$date} 07:30:00", $this->manager);
        $this->recordStockIn('Coffee Beans', 5000, 'Mt. Apo Coffee Traders', 'Arabica-Robusta Premium Blend', "{$date} 07:35:00", $this->manager);

        // Waste 1 (11:15 AM): Fresh Milk -500ml (curdled open container)
        $this->recordWaste('Fresh Milk', 500, 'Curdled / spoiled open bottle', 'High humidity temperature fluctuation', "{$date} 11:15:00", $this->manager);

        // Delivery 2 (14:00 PM): 16oz Cups +200, 22oz Cups +200, Lids +300, Straws +300
        $this->recordStockIn('16oz Cup', 200, 'EcoPack Supplies', '16oz paper cups batch replenishment', "{$date} 14:00:00", $this->manager);
        $this->recordStockIn('22oz Cup', 200, 'EcoPack Supplies', '22oz cold cups replenishment', "{$date} 14:05:00", $this->manager);
        $this->recordStockIn('Lid', 300, 'EcoPack Supplies', 'Sip-through flat lids', "{$date} 14:10:00", $this->manager);
        $this->recordStockIn('Straw', 300, 'EcoPack Supplies', 'PLA biodegradable straws', "{$date} 14:15:00", $this->manager);

        // Waste 2 (17:30 PM): 16oz Cup -5 pcs (crushed in shipping box)
        $this->recordWaste('16oz Cup', 5, 'Cracked / crushed carton batch', 'Damaged outer box sleeve', "{$date} 17:30:00", $this->manager);

        // Positive Adjustment (18:00 PM): Coffee Beans +350g (unopened pouch discovered during recount)
        $this->recordAdjustment('Coffee Beans', 350, 'Physical count recount correction; unopened foil bag discovered in dry storage', 'Authorized by Carlos Reyes', "{$date} 18:00:00", $this->supervisor);

        // Generate 40 Orders distributed realistically
        // Morning rush (07:00 - 10:00): 18 orders
        $this->generateOrdersRange($date, '07:05', '09:58', 18, $shift, [
            ['pwd' => true, 'id' => 'OSCA-2024-8841'], // Senior Discount Order
            ['refund' => true, 'reason' => 'Customer ordered wrong milk type, requested refund'] // 1 Refunded order
        ]);

        // Lunch bump (11:30 - 13:30): 12 orders
        $this->generateOrdersRange($date, '11:32', '13:28', 12, $shift, [
            ['custom_disc' => true, 'rate' => 15, 'reason' => 'Opening Week Courtesy']
        ]);

        // Afternoon & evening (14:00 - 20:50): 10 orders
        $this->generateOrdersRange($date, '14:10', '20:48', 10, $shift, []);

        // Close Shift 1 at 21:15 PM
        $shiftMetrics = $shift->calculateMetrics();
        $shift->update([
            'status' => 'closed',
            'closed_by' => 'anna',
            'closed_at' => Carbon::parse("{$date} 21:15:00", 'Asia/Manila'),
            'cash_in' => $shiftMetrics['cash_in'],
            'cash_out' => $shiftMetrics['cash_out'],
            'expected_cash' => $shiftMetrics['expected_cash'],
            'actual_cash' => $shiftMetrics['expected_cash'],
            'difference' => 0.00,
            'updated_at' => Carbon::parse("{$date} 21:15:00", 'Asia/Manila'),
        ]);

        AuditLog::log('shift_closed', 'shifts', "Shift closed by anna with expected ₱{$shiftMetrics['expected_cash']}", $this->cashier, 'shift', $shift->id, [
            'actual_cash' => $shiftMetrics['expected_cash'],
            'difference' => 0,
        ]);
        AuditLog::where('target_type', 'shift')->where('target_id', $shift->id)->update(['created_at' => Carbon::parse("{$date} 21:15:00", 'Asia/Manila')]);
    }

    /**
     * DAY 2: 2026-10-01 (Thursday)
     */
    protected function seedDayTwo(): void
    {
        $date = '2026-10-01';
        $this->command->info("Seeding Day 2: {$date}...");

        // Open Shift at 06:48 AM
        $shift = Shift::create([
            'opened_by' => 'anna',
            'opened_at' => Carbon::parse("{$date} 06:48:00", 'Asia/Manila'),
            'starting_cash' => 2000.00,
            'cash_in' => 0,
            'cash_out' => 0,
            'expected_cash' => 2000.00,
            'status' => 'open',
            'authorized_by' => $this->supervisor->id,
            'created_at' => Carbon::parse("{$date} 06:48:00", 'Asia/Manila'),
            'updated_at' => Carbon::parse("{$date} 06:48:00", 'Asia/Manila'),
        ]);

        // Delivery 1 (07:30 AM): Fresh Milk +12,000ml, Condensed Milk +4,000ml, Caramel Syrup +2,000ml, Vanilla Syrup +2,000ml
        $this->recordStockIn('Fresh Milk', 12000, 'Davao Dairy Farm', 'Scheduled morning dairy supply', "{$date} 07:30:00", $this->manager);
        $this->recordStockIn('Condensed Milk', 4000, 'Barista Essentials Co', 'Spanish latte condensed milk cartons', "{$date} 07:32:00", $this->manager);
        $this->recordStockIn('Caramel Syrup', 2000, 'Monin PH Distributor', 'Salted caramel syrup bottles', "{$date} 07:35:00", $this->manager);
        $this->recordStockIn('Vanilla Syrup', 2000, 'Monin PH Distributor', 'French vanilla syrup bottles', "{$date} 07:38:00", $this->manager);

        // Waste 1 (10:30 AM): Vanilla Syrup -150ml (dispenser nozzle leakage)
        $this->recordWaste('Vanilla Syrup', 150, 'Pump leak / dispenser overflow', 'Faulty pump valve replaced', "{$date} 10:30:00", $this->manager);

        // Delivery 2 (14:30 PM): Coffee Beans +5,000g, 12oz Cups +150, Ice +15,000g
        $this->recordStockIn('Coffee Beans', 5000, 'Mt. Apo Coffee Traders', 'Espresso roast whole beans', "{$date} 14:30:00", $this->manager);
        $this->recordStockIn('12oz Cup', 150, 'EcoPack Supplies', '12oz hot cups', "{$date} 14:35:00", $this->manager);
        $this->recordStockIn('Ice', 15000, 'Arctic Ice Co', 'Food-grade tube ice delivery', "{$date} 14:40:00", $this->manager);

        // Waste 2 (18:45 PM): Ice -2,000g (bin melt / power trip)
        $this->recordWaste('Ice', 2000, 'Bin melt / power trip', 'Ice well melt-off before evening rush', "{$date} 18:45:00", $this->manager);

        // Negative Adjustment (18:00 PM): Caramel Syrup -200ml (tare scale recalibration discrepancy)
        $this->recordAdjustment('Caramel Syrup', -200, 'Discrepancy audit; tare scale calibration error', 'Authorized by Carlos Reyes', "{$date} 18:00:00", $this->supervisor);

        // Generate 45 Orders
        // Morning rush (07:10 - 10:00): 20 orders
        $this->generateOrdersRange($date, '07:12', '09:55', 20, $shift, [
            ['pwd' => true, 'id' => 'PWD-DVO-5519'],
        ]);

        // Lunch rush (11:30 - 13:40): 14 orders including 1 big group order (6 drinks)
        $this->generateOrdersRange($date, '11:35', '13:38', 13, $shift, [
            ['pwd' => true, 'id' => 'OSCA-2023-1120'],
            ['custom_disc' => true, 'rate' => 10, 'reason' => 'Barista Club 10%'],
        ]);
        // Insert Big Group Order at 12:45 PM
        $this->createBigGroupOrder($date, '12:45:10', $shift);

        // Afternoon & evening (14:15 - 20:45): 11 orders
        $this->generateOrdersRange($date, '14:20', '20:42', 11, $shift, [
            ['refund' => true, 'reason' => 'Spilled on counter during handoff, customer requested refund']
        ]);

        // Close Shift 2 at 21:10 PM
        $shiftMetrics = $shift->calculateMetrics();
        $shift->update([
            'status' => 'closed',
            'closed_by' => 'anna',
            'closed_at' => Carbon::parse("{$date} 21:10:00", 'Asia/Manila'),
            'cash_in' => $shiftMetrics['cash_in'],
            'cash_out' => $shiftMetrics['cash_out'],
            'expected_cash' => $shiftMetrics['expected_cash'],
            'actual_cash' => $shiftMetrics['expected_cash'],
            'difference' => 0.00,
            'updated_at' => Carbon::parse("{$date} 21:10:00", 'Asia/Manila'),
        ]);

        AuditLog::log('shift_closed', 'shifts', "Shift closed by anna with expected ₱{$shiftMetrics['expected_cash']}", $this->cashier, 'shift', $shift->id, [
            'actual_cash' => $shiftMetrics['expected_cash'],
            'difference' => 0,
        ]);
        AuditLog::where('target_type', 'shift')->where('target_id', $shift->id)->update(['created_at' => Carbon::parse("{$date} 21:10:00", 'Asia/Manila')]);
    }

    /**
     * DAY 3: 2026-10-02 (Friday, Today - In Progress up to 11:30 AM)
     */
    protected function seedDayThree(): void
    {
        $date = '2026-10-02';
        $this->command->info("Seeding Day 3 (Today): {$date} up to 11:30 AM...");

        // Open Shift at 06:50 AM (REMAINS OPEN!)
        $shift = Shift::create([
            'opened_by' => 'anna',
            'opened_at' => Carbon::parse("{$date} 06:50:00", 'Asia/Manila'),
            'starting_cash' => 2000.00,
            'cash_in' => 0,
            'cash_out' => 0,
            'expected_cash' => 2000.00,
            'status' => 'open',
            'authorized_by' => $this->supervisor->id,
            'created_at' => Carbon::parse("{$date} 06:50:00", 'Asia/Manila'),
            'updated_at' => Carbon::parse("{$date} 06:50:00", 'Asia/Manila'),
        ]);

        // Delivery 1 (07:15 AM): Fresh Milk +10,000ml, Ice +15,000g
        $this->recordStockIn('Fresh Milk', 10000, 'Davao Dairy Farm', 'Friday weekend pre-stock delivery', "{$date} 07:15:00", $this->manager);
        $this->recordStockIn('Ice', 15000, 'Arctic Ice Co', 'Morning ice delivery', "{$date} 07:18:00", $this->manager);

        // Waste 1 (09:20 AM): Fresh Milk -300ml (dropped jug during rush)
        $this->recordWaste('Fresh Milk', 300, 'Dropped container during rush', 'Accidental drop behind counter', "{$date} 09:20:00", $this->manager);

        // Delivery 2 (10:45 AM): Coffee Beans +4,000g, Whipped Cream +1,500g
        $this->recordStockIn('Coffee Beans', 4000, 'Mt. Apo Coffee Traders', 'Coffee beans restocking', "{$date} 10:45:00", $this->manager);
        $this->recordStockIn('Whipped Cream', 1500, 'Anchor Dairy Supplies', 'Aerosol whipped cream cans', "{$date} 10:50:00", $this->manager);

        // Generate 28 Orders from 07:05 AM to 11:28 AM
        $this->generateOrdersRange($date, '07:05', '11:28', 28, $shift, [
            ['pwd' => true, 'id' => 'OSCA-2025-9932'],
            ['refund' => true, 'reason' => 'Customer had urgent work call, cancelled order before preparation']
        ]);

        // Update shift metrics (stays OPEN)
        $shiftMetrics = $shift->calculateMetrics();
        $shift->update([
            'cash_in' => $shiftMetrics['cash_in'],
            'cash_out' => $shiftMetrics['cash_out'],
            'expected_cash' => $shiftMetrics['expected_cash'],
            'updated_at' => Carbon::parse("{$date} 11:30:00", 'Asia/Manila'),
        ]);
    }

    /**
     * Record stock in with audit log and explicit timestamp
     */
    protected function recordStockIn(string $ingName, float $qty, string $supplier, string $notes, string $dateTimeStr, User $user): void
    {
        $ing = Ingredient::where('name', $ingName)->firstOrFail();
        $prev = (float) $ing->current_stock;
        $new = $prev + $qty;
        $ing->update(['current_stock' => $new]);

        $dt = Carbon::parse($dateTimeStr, 'Asia/Manila');

        $txn = InventoryTransaction::create([
            'ingredient_id' => $ing->id,
            'type' => 'stock_in',
            'quantity' => $qty,
            'previous_stock' => $prev,
            'new_stock' => $new,
            'supplier' => $supplier,
            'notes' => $notes,
            'performed_by' => $user->id,
            'created_at' => $dt,
            'updated_at' => $dt,
        ]);

        AuditLog::log('stock_in', 'inventory', "Stock in: {$qty} {$ing->unit} of {$ing->name}", $user, 'ingredient', $ing->id, [
            'quantity' => $qty,
            'supplier' => $supplier,
        ]);
        AuditLog::where('target_type', 'ingredient')->where('target_id', $ing->id)->latest()->first()?->update(['created_at' => $dt]);
    }

    /**
     * Record waste with audit log and explicit timestamp
     */
    protected function recordWaste(string $ingName, float $qty, string $reason, string $notes, string $dateTimeStr, User $user): void
    {
        $ing = Ingredient::where('name', $ingName)->firstOrFail();
        $prev = (float) $ing->current_stock;
        $new = max(0, $prev - $qty);
        $ing->update(['current_stock' => $new]);

        $dt = Carbon::parse($dateTimeStr, 'Asia/Manila');

        InventoryTransaction::create([
            'ingredient_id' => $ing->id,
            'type' => 'waste',
            'quantity' => $qty,
            'previous_stock' => $prev,
            'new_stock' => $new,
            'reason' => $reason,
            'notes' => $notes,
            'performed_by' => $user->id,
            'created_at' => $dt,
            'updated_at' => $dt,
        ]);

        AuditLog::log('waste_recorded', 'inventory', "Waste: {$qty} {$ing->unit} of {$ing->name} - {$reason}", $user, 'ingredient', $ing->id, [
            'quantity' => $qty,
            'reason' => $reason,
        ]);
        AuditLog::where('target_type', 'ingredient')->where('target_id', $ing->id)->latest()->first()?->update(['created_at' => $dt]);
    }

    /**
     * Record inventory adjustment with audit log and explicit timestamp
     */
    protected function recordAdjustment(string $ingName, float $qty, string $reason, string $notes, string $dateTimeStr, User $user): void
    {
        $ing = Ingredient::where('name', $ingName)->firstOrFail();
        $prev = (float) $ing->current_stock;
        $new = max(0, $prev + $qty);
        $ing->update(['current_stock' => $new]);

        $dt = Carbon::parse($dateTimeStr, 'Asia/Manila');

        InventoryTransaction::create([
            'ingredient_id' => $ing->id,
            'type' => 'adjustment',
            'quantity' => $qty, // signed
            'previous_stock' => $prev,
            'new_stock' => $new,
            'reason' => $reason,
            'notes' => $notes,
            'performed_by' => $user->id,
            'created_at' => $dt,
            'updated_at' => $dt,
        ]);

        AuditLog::log('stock_adjusted', 'inventory', "Adjustment: {$qty} {$ing->unit} of {$ing->name} - {$reason}", $user, 'ingredient', $ing->id, [
            'quantity' => $qty,
            'reason' => $reason,
        ]);
        AuditLog::where('target_type', 'ingredient')->where('target_id', $ing->id)->latest()->first()?->update(['created_at' => $dt]);
    }

    /**
     * Generate an array of orders within a time range on a date
     */
    protected function generateOrdersRange(string $date, string $startTime, string $endTime, int $count, Shift $shift, array $specialOrders = []): void
    {
        $start = Carbon::parse("{$date} {$startTime}:00", 'Asia/Manila');
        $end = Carbon::parse("{$date} {$endTime}:00", 'Asia/Manila');
        $intervalSeconds = (int) ($start->diffInSeconds($end) / max(1, $count));

        $menuCombos = [
            ['product' => 'Americano', 'size' => '16oz', 'addons' => []],
            ['product' => 'Café Latte', 'size' => '16oz', 'addons' => ['Vanilla Syrup']],
            ['product' => 'Spanish Latte', 'size' => '16oz', 'addons' => []],
            ['product' => 'Spanish Latte', 'size' => '22oz', 'addons' => ['Extra Shot']],
            ['product' => 'Iced Latte', 'size' => '16oz', 'addons' => ['Caramel Syrup']],
            ['product' => 'Iced Latte', 'size' => '22oz', 'addons' => []],
            ['product' => 'Cappuccino', 'size' => '16oz', 'addons' => []],
            ['product' => 'Mocha', 'size' => '16oz', 'addons' => ['Whipped Cream']],
            ['product' => 'Caramel Frappe', 'size' => '16oz', 'addons' => ['Whipped Cream']],
            ['product' => 'Matcha Latte', 'size' => '16oz', 'addons' => []],
            ['product' => 'Matcha Latte', 'size' => '22oz', 'addons' => ['Extra Milk']],
            ['product' => 'Americano', 'size' => '12oz', 'addons' => []],
            ['product' => 'Iced Americano', 'size' => '16oz', 'addons' => []],
            ['product' => 'Chocolate Latte', 'size' => '16oz', 'addons' => ['Whipped Cream']],
            ['product' => 'Mocha Frappe', 'size' => '16oz', 'addons' => ['Hazelnut Syrup']],
            ['product' => 'Café Latte', 'size' => '12oz', 'addons' => ['Hazelnut Syrup']],
        ];

        for ($i = 0; $i < $count; $i++) {
            $orderTime = (clone $start)->addSeconds($i * $intervalSeconds + rand(0, min(120, $intervalSeconds)));
            if ($orderTime->gt($end)) {
                $orderTime = clone $end;
            }

            // Check if special order requested
            $special = array_shift($specialOrders);

            // Determine items in order (mostly 1 or 2 items)
            $numItems = rand(1, 100) <= 75 ? 1 : 2;
            $orderItems = [];

            for ($k = 0; $k < $numItems; $k++) {
                $pick = $menuCombos[array_rand($menuCombos)];
                // Occasionally add Hazelnut syrup to steadily deplete it towards low stock threshold
                if (rand(1, 10) <= 3 && !in_array('Hazelnut Syrup', $pick['addons'])) {
                    $pick['addons'][] = 'Hazelnut Syrup';
                }
                $orderItems[] = $pick;
            }

            // Payment method: ~65% cash, ~35% online
            $isOnline = rand(1, 100) > 65;
            $payMethod = $isOnline ? (rand(1, 2) === 1 ? 'gcash' : 'maya') : 'cash';

            $this->createOrder($orderTime, $shift, $orderItems, $payMethod, $special);
        }
    }

    /**
     * Create a single realistic order with real deductions and audit logging
     */
    protected function createOrder(Carbon $time, Shift $shift, array $itemsData, string $payMethod, ?array $special = null): Order
    {
        $dateStr = $time->format('Ymd');
        $this->orderSeq[$dateStr] = ($this->orderSeq[$dateStr] ?? 0) + 1;
        $orderNumber = "ORD-{$dateStr}-" . str_pad($this->orderSeq[$dateStr], 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            'order_number' => $orderNumber,
            'cashier_name' => 'anna',
            'user_id' => $this->cashier->id,
            'shift_id' => $shift->id,
            'status' => 'completed',
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        $subtotal = 0.00;
        $totalDiscount = 0.00;
        $vatableSales = 0.00;
        $vatExemptSales = 0.00;
        $totalTax = 0.00;
        $totalDue = 0.00;

        $products = Product::with('sizes')->get()->keyBy('name');
        $sizes = Size::all()->keyBy('name');
        $addonsMap = AddOn::all()->keyBy('name');

        foreach ($itemsData as $it) {
            $product = $products->get($it['product']);
            $size = $sizes->get($it['size']);
            if (!$product || !$size) continue;

            $productSize = $product->sizes->where('id', $size->id)->first()?->pivot;
            $unitPrice = $productSize ? (float) $productSize->price : 119.00;
            $qty = 1;
            $itemSubtotal = $unitPrice * $qty;

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'size_id' => $size->id,
                'product_name' => $product->name,
                'size_name' => $size->name,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'subtotal' => $itemSubtotal,
                'created_at' => $time,
                'updated_at' => $time,
            ]);

            // Add-ons
            foreach ($it['addons'] ?? [] as $addonName) {
                $addon = $addonsMap->get($addonName);
                if ($addon) {
                    OrderItemAddOn::create([
                        'order_item_id' => $orderItem->id,
                        'add_on_id' => $addon->id,
                        'add_on_name' => $addon->name,
                        'add_on_price' => $addon->price,
                    ]);
                    $itemSubtotal += (float) $addon->price;
                }
            }

            // Discounts
            $discType = 'none';
            $discRate = 0.00;
            $lineDiscount = 0.00;
            $isVatExempt = false;
            $lineTax = 0.00;
            $idNumber = null;

            if ($special && !empty($special['pwd'])) {
                // Senior/PWD 20% discount + VAT Exempt
                $discType = 'pwd_senior';
                $discRate = 20.00;
                $lineDiscount = round($itemSubtotal * 0.20, 2);
                $isVatExempt = true;
                $lineTax = 0.00;
                $idNumber = $special['id'] ?? 'OSCA-2024-DEMO';
            } elseif ($special && !empty($special['custom_disc'])) {
                // Custom Discount
                $pct = (float) ($special['rate'] ?? 10.00);
                $discType = 'custom_pct';
                $discRate = $pct;
                $lineDiscount = round($itemSubtotal * ($pct / 100), 2);
                $isVatExempt = false;
                $net = max(0, $itemSubtotal - $lineDiscount);
                $lineTax = round($net * 0.12, 2);
            } else {
                // Standard vatable
                $lineTax = round($itemSubtotal * 0.12, 2);
            }

            $lineTotal = round(max(0, $itemSubtotal - $lineDiscount) + $lineTax, 2);

            $orderItem->update([
                'subtotal' => $itemSubtotal,
                'discount_type' => $discType,
                'discount_rate' => $discRate,
                'discount' => $lineDiscount,
                'is_vat_exempt' => $isVatExempt,
                'tax' => $lineTax,
                'total' => $lineTotal,
                'id_number' => $idNumber,
            ]);

            $subtotal += $itemSubtotal;
            $totalDiscount += $lineDiscount;
            if ($isVatExempt) {
                $vatExemptSales += max(0, $itemSubtotal - $lineDiscount);
            } else {
                $vatableSales += max(0, $itemSubtotal - $lineDiscount);
            }
            $totalTax += $lineTax;
            $totalDue += $lineTotal;

            // Deduct recipe ingredients
            $this->deductRecipeForOrderItem($orderItem, $time);

            // Deduct add-on ingredients (syrups / milk / extra shot)
            foreach ($it['addons'] ?? [] as $addonName) {
                $this->deductAddonIngredient($addonName, $order->id, $time);
            }
        }

        // Finalize Order
        $order->update([
            'subtotal' => $subtotal,
            'discount' => $totalDiscount,
            'tax_rate' => 12.00,
            'tax' => $totalTax,
            'vatable_sales' => $vatableSales,
            'vat_exempt_sales' => $vatExemptSales,
            'total' => $totalDue,
        ]);

        // Payment
        $refNumber = null;
        if ($payMethod !== 'cash') {
            $prefix = strtoupper($payMethod);
            $refNumber = "{$prefix}-" . rand(100000000, 999999999);
            $tendered = $totalDue;
            $change = 0.00;
        } else {
            // Realistic cash denominations
            $tendered = $totalDue;
            if (rand(1, 100) > 40) {
                // Round up to nearest 50, 100, 200, 500, or 1000
                $bills = [50, 100, 200, 500, 1000];
                foreach ($bills as $b) {
                    if ($b >= $totalDue) {
                        $tendered = (float) $b;
                        break;
                    }
                }
            }
            $change = max(0, $tendered - $totalDue);
        }

        Payment::create([
            'order_id' => $order->id,
            'method' => $payMethod,
            'amount_tendered' => $tendered,
            'change' => $change,
            'reference_number' => $refNumber,
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        AuditLog::log(
            'order_completed',
            'orders',
            "Order {$order->order_number} completed by anna. Total: ₱" . number_format($order->total, 2),
            $this->cashier,
            'order',
            $order->id,
            ['total' => $order->total, 'payment_method' => $payMethod]
        );
        AuditLog::where('target_type', 'order')->where('target_id', $order->id)->update(['created_at' => $time]);

        // Process refund if specified
        if ($special && !empty($special['refund'])) {
            $refundTime = (clone $time)->addMinutes(rand(5, 20));
            $refundReason = $special['reason'] ?? 'Customer complaint / taste preference';

            Refund::create([
                'order_id' => $order->id,
                'refund_amount' => $order->total,
                'reason' => $refundReason,
                'cashier_name' => 'anna',
                'authorized_by' => $this->supervisor->id,
                'created_at' => $refundTime,
                'updated_at' => $refundTime,
            ]);

            $order->update(['status' => 'refunded', 'updated_at' => $refundTime]);

            AuditLog::log(
                'order_refunded',
                'orders',
                "Order {$order->order_number} refunded. Amount: ₱" . number_format($order->total, 2) . ". Reason: {$refundReason}. Authorized by: Carlos Reyes (supervisor)",
                $this->supervisor,
                'order',
                $order->id,
                ['refund_amount' => $order->total, 'reason' => $refundReason, 'authorized_by' => 'Carlos Reyes']
            );
            AuditLog::where('target_type', 'order')->where('target_id', $order->id)->latest()->first()?->update(['created_at' => $refundTime]);
        }

        return $order;
    }

    /**
     * Big group order on Day 2 lunch rush
     */
    protected function createBigGroupOrder(string $date, string $timeStr, Shift $shift): void
    {
        $time = Carbon::parse("{$date} {$timeStr}", 'Asia/Manila');
        $groupItems = [
            ['product' => 'Spanish Latte', 'size' => '22oz', 'addons' => ['Extra Shot']],
            ['product' => 'Caramel Frappe', 'size' => '22oz', 'addons' => ['Whipped Cream']],
            ['product' => 'Iced Latte', 'size' => '16oz', 'addons' => ['Vanilla Syrup']],
            ['product' => 'Mocha Frappe', 'size' => '16oz', 'addons' => ['Whipped Cream']],
            ['product' => 'Iced Americano', 'size' => '16oz', 'addons' => []],
            ['product' => 'Matcha Latte', 'size' => '22oz', 'addons' => ['Extra Milk', 'Pearl/Boba']],
        ];

        $this->createOrder($time, $shift, $groupItems, 'gcash', null);
    }

    /**
     * Deduct recipe ingredients for order line item and record transaction
     */
    protected function deductRecipeForOrderItem(OrderItem $item, Carbon $time): void
    {
        $recipe = Recipe::where('product_id', $item->product_id)
            ->where('size_id', $item->size_id)
            ->with('recipeIngredients.ingredient')
            ->first();

        if (!$recipe) return;

        foreach ($recipe->recipeIngredients as $ri) {
            $ing = $ri->ingredient;
            if (!$ing) continue;

            $qtyDeduct = $ri->quantity * $item->quantity;
            $prev = (float) $ing->current_stock;
            $new = max(0, $prev - $qtyDeduct);

            $ing->update(['current_stock' => $new]);

            InventoryTransaction::create([
                'ingredient_id' => $ing->id,
                'type' => 'sales_consumption',
                'quantity' => $qtyDeduct,
                'previous_stock' => $prev,
                'new_stock' => $new,
                'reference_type' => 'order',
                'reference_id' => $item->order_id,
                'performed_by' => $this->cashier->id,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        }
    }

    /**
     * Deduct raw ingredient used in add-ons
     */
    protected function deductAddonIngredient(string $addonName, int $orderId, Carbon $time): void
    {
        $map = [
            'Extra Shot'     => ['name' => 'Coffee Beans', 'qty' => 9],
            'Vanilla Syrup'  => ['name' => 'Vanilla Syrup', 'qty' => 20],
            'Caramel Syrup'  => ['name' => 'Caramel Syrup', 'qty' => 20],
            'Hazelnut Syrup' => ['name' => 'Hazelnut Syrup', 'qty' => 20],
            'Whipped Cream'  => ['name' => 'Whipped Cream', 'qty' => 25],
            'Extra Milk'     => ['name' => 'Fresh Milk', 'qty' => 60],
        ];

        if (!isset($map[$addonName])) return;

        $target = $map[$addonName];
        $ing = Ingredient::where('name', $target['name'])->first();
        if (!$ing) return;

        $prev = (float) $ing->current_stock;
        $qtyDeduct = (float) $target['qty'];
        $new = max(0, $prev - $qtyDeduct);

        $ing->update(['current_stock' => $new]);

        InventoryTransaction::create([
            'ingredient_id' => $ing->id,
            'type' => 'sales_consumption',
            'quantity' => $qtyDeduct,
            'previous_stock' => $prev,
            'new_stock' => $new,
            'reference_type' => 'order',
            'reference_id' => $orderId,
            'notes' => "Add-on: {$addonName}",
            'performed_by' => $this->cashier->id,
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }

    /**
     * Ensure exactly 2 ingredients trigger low_stock alerts believably
     */
    protected function generateFinalAlerts(): void
    {
        $ingredients = Ingredient::all();
        foreach ($ingredients as $ing) {
            if ($ing->current_stock <= $ing->minimum_stock && $ing->current_stock > 0) {
                SystemNotification::create([
                    'type' => 'low_stock',
                    'title' => 'Low Stock Alert',
                    'message' => "{$ing->name} is running low ({$ing->current_stock} {$ing->unit} remaining, minimum: {$ing->minimum_stock} {$ing->unit})",
                    'data' => [
                        'ingredient_id' => $ing->id,
                        'current_stock' => $ing->current_stock,
                        'minimum_stock' => $ing->minimum_stock,
                    ],
                    'target_role' => 'manager',
                    'created_at' => Carbon::parse('2026-10-02 11:30:00', 'Asia/Manila'),
                    'updated_at' => Carbon::parse('2026-10-02 11:30:00', 'Asia/Manila'),
                ]);
            }
        }
    }
}

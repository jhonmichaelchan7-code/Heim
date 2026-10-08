<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::beginTransaction();
            try {
                DB::statement('
                    CREATE TABLE orders_with_voided_status (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    order_number VARCHAR NOT NULL UNIQUE,
                    cashier_name VARCHAR NOT NULL,
                    user_id INTEGER NULL,
                    subtotal NUMERIC NOT NULL DEFAULT 0,
                    discount NUMERIC NOT NULL DEFAULT 0,
                    tax_rate NUMERIC NOT NULL DEFAULT 12,
                    tax NUMERIC NOT NULL DEFAULT 0,
                    total NUMERIC NOT NULL DEFAULT 0,
                    status VARCHAR NOT NULL DEFAULT \'pending\',
                    notes TEXT NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL,
                    shift_id INTEGER NULL,
                    vatable_sales NUMERIC NOT NULL DEFAULT 0,
                    vat_exempt_sales NUMERIC NOT NULL DEFAULT 0,
                    branch_id INTEGER NULL,
                    order_type VARCHAR NOT NULL DEFAULT \'dine_in\',
                    grab_order_code VARCHAR NULL,
                    rider_code VARCHAR NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                    )
                ');
                DB::statement('
                    INSERT INTO orders_with_voided_status (
                    id, order_number, cashier_name, user_id, subtotal, discount, tax_rate, tax,
                    total, status, notes, created_at, updated_at, shift_id, vatable_sales,
                    vat_exempt_sales, branch_id, order_type, grab_order_code, rider_code
                )
                SELECT
                    id, order_number, cashier_name, user_id, subtotal, discount, tax_rate, tax,
                    total, status, notes, created_at, updated_at, shift_id, vatable_sales,
                    vat_exempt_sales, branch_id, order_type, grab_order_code, rider_code
                    FROM orders
                ');
                DB::statement('DROP TABLE orders');
                DB::statement('ALTER TABLE orders_with_voided_status RENAME TO orders');
                DB::commit();
            } catch (\Throwable $exception) {
                DB::rollBack();
                throw $exception;
            } finally {
                DB::statement('PRAGMA foreign_keys = ON');
            }

            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
            DB::statement('ALTER TABLE orders ALTER COLUMN status TYPE VARCHAR(255) USING status::text');
            DB::statement("ALTER TABLE orders ALTER COLUMN status SET DEFAULT 'pending'");

            return;
        }

        throw new \RuntimeException("Unsupported database driver for voided order status: {$driver}");
    }

    public function down(): void
    {
        // Keep the relaxed status column so existing voided-order history stays valid.
    }
};

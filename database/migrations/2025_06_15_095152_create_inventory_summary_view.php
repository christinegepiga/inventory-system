<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS inventory_summary');

        DB::statement("
            CREATE VIEW inventory_summary AS
            SELECT 
                p.id,
                p.name,
                p.sku,
                p.price,
                p.initial_quantity + COALESCE(SUM(
                    CASE 
                        WHEN im.type = 'in' THEN im.quantity
                        WHEN im.type = 'out' THEN -im.quantity
                    END
                ), 0) AS current_quantity,
                p.initial_quantity,
                COUNT(im.id) AS movement_count
            FROM products p
            LEFT JOIN inventory_movements im ON p.id = im.product_id
            GROUP BY p.id, p.name, p.sku, p.price, p.initial_quantity
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS inventory_summary');
    }
};

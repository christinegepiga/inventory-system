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
        // Drop existing procedures and triggers if they exist
        DB::unprepared('DROP PROCEDURE IF EXISTS GetProductInventoryHistory');
        DB::unprepared('DROP TRIGGER IF EXISTS after_movement_insert');

        // Create the stored procedure
        DB::unprepared('
            CREATE PROCEDURE GetProductInventoryHistory(IN product_id INT)
            BEGIN
                SELECT 
                    p.name,
                    p.sku,
                    im.movement_date,
                    im.type,
                    im.quantity,
                    im.reason,
                    p.initial_quantity + SUM(
                        CASE 
                            WHEN im.type = "in" THEN im.quantity
                            WHEN im.type = "out" THEN -im.quantity
                        END
                    ) OVER (PARTITION BY im.product_id ORDER BY im.movement_date, im.id) AS current_quantity
                FROM inventory_movements im
                JOIN products p ON p.id = im.product_id
                WHERE im.product_id = product_id
                ORDER BY im.movement_date DESC, im.id DESC;
            END
        ');

        // Create the trigger
        DB::unprepared('
            CREATE TRIGGER after_movement_insert
            BEFORE INSERT ON inventory_movements
            FOR EACH ROW
            BEGIN
                DECLARE current_qty INT;
                DECLARE initial_qty INT;
                DECLARE product_name VARCHAR(255);
                
                -- Get product info
                SELECT initial_quantity, name INTO initial_qty, product_name
                FROM products
                WHERE id = NEW.product_id;
                
                -- Calculate current quantity
                SELECT initial_qty + COALESCE(SUM(
                    CASE 
                        WHEN type = "in" THEN quantity
                        WHEN type = "out" THEN -quantity
                    END
                ), 0) INTO current_qty
                FROM inventory_movements
                WHERE product_id = NEW.product_id;
                
                -- Prevent negative inventory
                IF NEW.type = "out" AND (current_qty - NEW.quantity) < 0 THEN
                    SET @error_msg = CONCAT(
                        \'Insufficient stock for \', 
                        product_name, 
                        \'. Available: \', 
                        current_qty
                    );
                    SIGNAL SQLSTATE \'45000\' 
                    SET MESSAGE_TEXT = @error_msg;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GetProductInventoryHistory');
        DB::unprepared('DROP TRIGGER IF EXISTS after_movement_insert');
    }
};

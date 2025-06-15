<?php

// app/Livewire/InventoryManager.php
namespace App\Livewire;

use App\Models\Product;
use App\Models\InventoryMovement;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class InventoryManager extends Component
{
    public $products = [];
    public $movements = [];
    public $productForm = [
        'name' => '',
        'sku' => '',
        'description' => '',
        'price' => '',
        'initial_quantity' => 0
    ];
    public $movementForm = [
        'product_id' => '',
        'quantity' => 1,
        'type' => 'in',
        'reason' => ''
    ];
    public $selectedProductId = null;
    public $showMovementHistory = false;
    public $historyData = [];
    public $optimizationResults = [];

    public function mount()
    {
        $this->loadProducts();
    }

    public function loadProducts()
    {
        // Using the view we created
        $this->products = DB::table('inventory_summary')->get()->toArray();
    }

    public function createProduct()
    {
        $this->validate([
            'productForm.name' => 'required|string|max:255',
            'productForm.sku' => 'required|string|max:50|unique:products,sku',
            'productForm.price' => 'required|numeric|min:0',
            'productForm.initial_quantity' => 'required|integer|min:0'
        ]);

        DB::transaction(function () {
            try {
                Product::create($this->productForm);
                $this->productForm = [
                    'name' => '',
                    'sku' => '',
                    'description' => '',
                    'price' => '',
                    'initial_quantity' => 0
                ];
                $this->loadProducts();
            } catch (\Exception $e) {
                throw $e;
            }
        });
    }

    public function recordMovement()
    {
        $this->validate([
            'movementForm.product_id' => 'required|exists:products,id',
            'movementForm.quantity' => 'required|integer|min:1',
            'movementForm.type' => 'required|in:in,out',
            'movementForm.reason' => 'required|string|max:255'
        ]);

        DB::transaction(function () {
            try {
                InventoryMovement::create($this->movementForm);
                $this->movementForm = [
                    'product_id' => '',
                    'quantity' => 1,
                    'type' => 'in',
                    'reason' => ''
                ];
                $this->loadProducts();
            } catch (\Exception $e) {
                throw $e;
            }
        });
    }

    public function showHistory($productId)
    {
        $this->selectedProductId = $productId;
        $this->showMovementHistory = true;
        
        // Using the stored procedure via raw query
        $this->historyData = DB::select('CALL GetProductInventoryHistory(?)', [$productId]);
    }

    public function testQueryOptimization()
    {
        // Test without index
        $start = microtime(true);
        DB::table('inventory_movements')->where('movement_date', '>', now()->subYear())->get();
        $withoutIndex = microtime(true) - $start;
        
        // Test with index (should use the movement_date index we created)
        $start = microtime(true);
        DB::table('inventory_movements')
            ->where('movement_date', '>', now()->subYear())
            ->orderBy('movement_date')
            ->get();
        $withIndex = microtime(true) - $start;
        
        $this->optimizationResults = [
            'without_index' => $withoutIndex,
            'with_index' => $withIndex,
            'improvement' => ($withoutIndex - $withIndex) / $withoutIndex * 100
        ];
    }

    public function render()
    {
        return view('livewire.inventory-manager')
            ->layout('components.layouts.app');
    }
}
<?php

// app/Livewire/InventoryManager.php
namespace App\Livewire;

use App\Models\Product;
use App\Models\InventoryMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class InventoryManager extends Component
{
    use WithPagination;

    public $perPage = 10; // Number of products per page
    public $allProducts = []; 

    public $movements = [];
    public $productForm = [
        'name' => '',
        'sku' => '',
        'description' => '',
        'price' => '',
        'initial_quantity' => 1
    ];
    public $editingProductId = null;
    public $editProductForm = [
        'name' => '',
        'sku' => '',
        'description' => '',
        'price' => '',
        'initial_quantity' => 1
    ];

    public function editProduct($productId)
    {
        $this->editingProductId = $productId;
        $product = Product::find($productId);
        $this->editProductForm = [
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => $product->description,
            'price' => $product->price,
            'initial_quantity' => $product->initial_quantity
        ];
    }

    public function updateProduct()
    {
        $this->validate([
            'editProductForm.name' => 'required|string|max:255',
            'editProductForm.sku' => 'required|string|max:50|unique:products,sku,'.$this->editingProductId,
            'editProductForm.price' => 'required|numeric|min:0',
            'editProductForm.initial_quantity' => 'required|integer|min:0'
        ]);

        DB::transaction(function () {
            try {
                $product = Product::find($this->editingProductId);
                $product->update($this->editProductForm);
                $this->cancelEdit();
            } catch (\Exception $e) {
                throw $e;
            }
        });
    }

    public function deleteProduct($productId)
    {
        DB::transaction(function () use ($productId) {
            try {
                $product = Product::findOrFail($productId);
                
                // Delete related movements first
                $product->movements()->delete();
                
                // Then delete the product
                $product->delete();
                
            } catch (\Exception $e) {
                $this->addError('delete', 'Failed to delete product: ' . $e->getMessage());
            }
        });
    }
    

    public function cancelEdit()
    {
        $this->editingProductId = null;
        $this->reset('editProductForm');
    }

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
        // Load all products for the movement form
        $this->allProducts = \App\Models\Product::orderBy('name')->get();
    }

    public function updating($name, $value)
    {
        // Reset to first page when searching/filtering
        if ($name === 'search') {
            $this->resetPage();
        }
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

        try {
            DB::transaction(function () {
                // Check stock before attempting transaction
                if ($this->movementForm['type'] === 'out') {
                    $product = Product::find($this->movementForm['product_id']);
                    $currentStock = $product->current_quantity;
                    
                    if ($this->movementForm['quantity'] > $currentStock) {
                        $productName = $product->name ?? 'Unknown Product';
                        throw new \Exception(
                            "Cannot process transaction for '{$productName}'. Only {$currentStock} items available."
                        );
                    }
                }

                InventoryMovement::create($this->movementForm);
                
                // Reset form
                $this->movementForm = [
                    'product_id' => '',
                    'quantity' => 1,
                    'type' => 'in',
                    'reason' => ''
                ];
                
                // Show success message
                $this->dispatch('showToast', 
                    type: 'success',
                    message: 'Transaction recorded successfully.',
                    duration: 5000
                );
            });
        } catch (\Exception $e) {
            $this->dispatch('showToast', 
                type: 'error',
                message: $e->getMessage(),
                duration: 5000
            );
        }
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
        $products = DB::table('inventory_summary')->paginate($this->perPage);

        return view('livewire.inventory-manager', [
            'products' => $products,
            'allProducts' => $this->allProducts, // Pass to view
        ])->layout('components.layouts.app');
    }
}
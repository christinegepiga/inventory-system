<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Inventory Management System</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Product Form -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Add New Product</h2>
            <form wire:submit.prevent="createProduct">
                <div class="mb-4">
                    <label class="block text-gray-700">Name</label>
                    <input type="text" wire:model="productForm.name" class="w-full p-2 border rounded">
                    @error('productForm.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">SKU</label>
                    <input type="text" wire:model="productForm.sku" class="w-full p-2 border rounded">
                    @error('productForm.sku') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Description</label>
                    <textarea wire:model="productForm.description" class="w-full p-2 border rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Price</label>
                    <input type="number" step="0.01" wire:model="productForm.price" class="w-full p-2 border rounded">
                    @error('productForm.price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Initial Quantity</label>
                    <input type="number" wire:model="productForm.initial_quantity" class="w-full p-2 border rounded">
                    @error('productForm.initial_quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Product</button>
            </form>
        </div>
        
        <!-- Movement Form -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Record Inventory Movement</h2>
            <form wire:submit.prevent="recordMovement">
                <div class="mb-4">
                    <label class="block text-gray-700">Product</label>
                    <select wire:model="movementForm.product_id" class="w-full p-2 border rounded">
                        <option value="">Select Product</option>
                        @foreach($allProducts as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                    @error('movementForm.product_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Quantity</label>
                    <input type="number" wire:model="movementForm.quantity" class="w-full p-2 border rounded">
                    @error('movementForm.quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Type</label>
                    <select wire:model="movementForm.type" class="w-full p-2 border rounded">
                        <option value="in">Stock In</option>
                        <option value="out">Stock Out</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Reason</label>
                    <input type="text" wire:model="movementForm.reason" class="w-full p-2 border rounded">
                    @error('movementForm.reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Record Movement</button>
            </form>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="mt-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Current Inventory</h2>
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">SKU</th>
                    <th class="p-3 text-right">Price</th>
                    <th class="p-3 text-right">Current Qty</th>
                    <th class="p-3 text-right">Initial Qty</th>
                    <th class="p-3 text-right">Movements</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-t hover:bg-gray-50" wire:key="product-{{ $product->id }}">
                        @if($editingProductId === $product->id)
                            <td colspan="7" class="p-4 bg-gray-50">
                                <form wire:submit.prevent="updateProduct" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                    <div>
                                        <label class="block text-sm text-gray-600">Name</label>
                                        <input type="text" wire:model="editProductForm.name" class="w-full p-2 border rounded">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600">SKU</label>
                                        <input type="text" wire:model="editProductForm.sku" class="w-full p-2 border rounded">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600">Price</label>
                                        <input type="number" step="0.01" wire:model="editProductForm.price" class="w-full p-2 border rounded">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600">Initial Qty</label>
                                        <input type="number" wire:model="editProductForm.initial_quantity" class="w-full p-2 border rounded">
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">Update</button>
                                        <button wire:click="cancelEdit" type="button" class="bg-gray-500 text-white px-3 py-1 rounded text-sm">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        @else
                            <td class="p-3">{{ $product->name }}</td>
                            <td class="p-3">{{ $product->sku }}</td>
                            <td class="p-3 text-right">₱{{ number_format($product->price, 2) }}</td>
                            <td class="p-3 text-right">{{ number_format($product->current_quantity,1) }}</td>
                            <td class="p-3 text-right">{{ number_format($product->initial_quantity,1) }}</td>
                            <td class="p-3 text-right">{{ $product->movement_count }}</td>
                            <td class="p-3 text-right space-x-2">
                                <button wire:click="editProduct({{ $product->id }})" class="text-blue-500 hover:text-blue-700">
                                    Edit
                                </button>
                                <button
                                    class="text-red-500 hover:text-red-700"
                                    type="button"
                                    wire:click="deleteProduct(@js($product->id))"
                                    wire:confirm="Are you sure you want to delete '{{ $product->name }}'? \n\nThis action cannot be undone and will also remove all inventory history for this product."
                                >
                                    Delete 
                                </button>

                                <button wire:click="showHistory({{ $product->id }})" class="text-green-500 hover:text-green-700">
                                    History
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="text-center"><em>No products added yet...</em></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @if(!blank($products))
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
    @endif
    
    <!-- Movement History Modal -->
    @if($showMovementHistory)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-[80vh] overflow-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Inventory History</h2>
                        <button wire:click="$set('showMovementHistory', false)" class="text-gray-500 hover:text-gray-700">
                            &times;
                        </button>
                    </div>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 text-left">Date</th>
                                <th class="p-3 text-left">Type</th>
                                <th class="p-3 text-right">Quantity</th>
                                <th class="p-3 text-right">Current Qty</th>
                                <th class="p-3 text-left">Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historyData as $record)
                                <tr class="border-t">
                                    <td class="p-3">{{ \Carbon\Carbon::parse($record->movement_date)->format('Y-m-d H:i') }}</td>
                                    <td class="p-3">
                                        <span class="{{ $record->type === 'in' ? 'text-green-500' : 'text-red-500' }}">
                                            {{ ucfirst($record->type) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">{{ $record->quantity }}</td>
                                    <td class="p-3 text-right">{{ $record->current_quantity }}</td>
                                    <td class="p-3">{{ $record->reason }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Query Optimization Test -->
    <div class="mt-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Query Optimization Test</h2>
        <p class="mb-4">This tests the performance difference with and without using the index on movement_date.</p>
        <button wire:click="testQueryOptimization" class="bg-purple-500 text-white px-4 py-2 rounded mb-4">
            Run Optimization Test
        </button>
        
        @if(!empty($optimizationResults))
            <div class="bg-gray-100 p-4 rounded">
                <h3 class="font-semibold mb-2">Results:</h3>
                <p>Without index: {{ number_format($optimizationResults['without_index'] * 1000, 2) }} ms</p>
                <p>With index: {{ number_format($optimizationResults['with_index'] * 1000, 2) }} ms</p>
                <p class="font-semibold mt-2">
                    Improvement: {{ number_format($optimizationResults['improvement'], 2) }}% faster
                </p>
            </div>
        @endif
    </div>
</div>
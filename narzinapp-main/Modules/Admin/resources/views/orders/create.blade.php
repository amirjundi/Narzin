<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'user_id',
                'label' => 'User',
                'type' => 'select',
                'width' => 'half',
                'options' => $users,
                'optionLabel' => 'name',
                'value' => old('user_id'),
            ],
            [
                'name' => 'address_id',
                'label' => 'Address',
                'type' => 'select',
                'width' => 'half',
                'options' => $addresses,
                'optionLabel' => 'address',
                'value' => old('address_id'),
            ],
            [
                'name' => 'payment_status',
                'label' => 'Payment Status',
                'type' => 'select',
                'width' => 'half',
                'options' => $statuses,
                'optionLabel' => 'name',
                'value' => old('payment_status'),
            ],
            [
                'name' => 'order_status',
                'label' => 'Order Status',
                'type' => 'select',
                'width' => 'half',
                'options' => $statuses,
                'optionLabel' => 'name',
                'value' => old('order_status'),
            ],
            [
                'name' => 'sipping_type',
                'label' => 'Shipping Type',
                'type' => 'select',
                'options' => ['Fast', 'Normal'],
                'width' => 'half',
                'value' => old('sipping_type'),
            ],
        ];
    @endphp

    <x-forms.form routePrefix="orders" :action="route('orders.store')" :columns="$formColumns" method="POST">

        <div class="mt-8">
            <div class="bg-white rounded-md shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Order Items</h3>

                <!-- Add New Item Button -->
                <button type="button" onclick="addNewItem()"
                    class="mb-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Item
                </button>

                <!-- Items Container -->
                <div id="orderItemsContainer">
                    <!-- Items will be added here dynamically -->
                </div>

                <!-- Order Total -->
                <div class="mt-6 text-right">
                    <span class="text-lg font-semibold text-gray-700">Total: $</span>
                    <span id="orderTotal">0.00</span>
                    <input type="hidden" name="total_amount" id="totalAmountInput" value="0.00">
                </div>
            </div>
        </div>

        <script>
         const products = @json($products->map(function($product) {
    return [
        'id' => $product->id,
        'name' => $product->name_german
    ];
}));

// Order Management Class
class OrderManager {
    constructor() {
        this.container = document.getElementById('orderItemsContainer');
        this.bindEvents();
    }

    bindEvents() {
        // Add event listener for form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', this.validateForm.bind(this));
        }
    }

    validateForm(event) {
        const items = this.container.querySelectorAll('.order-item');
        if (items.length === 0) {
            event.preventDefault();
            alert('Please add at least one item to the order.');
            return false;
        }
        return true;
    }

    generateItemTemplate(itemCount) {
        const productOptions = products.map(product => 
            `<option value="${product.id}">${product.name}</option>`
        ).join('');

        return `
            <div class="order-item bg-gray-50 p-4 rounded-md mb-4">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <!-- Product Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Product</label>
                        <select name="items[${itemCount}][product_id]" 
                                onchange="orderManager.handleProductChange(this)"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Product</option>
                            ${productOptions}
                        </select>
                    </div>

                    <!-- Variant Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Variant</label>
                        <select name="items[${itemCount}][product_variant_id]" 
                                onchange="orderManager.handleVariantChange(this)"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Variant</option>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" 
                               name="items[${itemCount}][quantity]"
                               value="1"
                               onchange="orderManager.handleQuantityChange(this)"
                               min="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Unit Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                        <input type="number" 
                               name="items[${itemCount}][unit_price]"
                               value="0.00"
                               step="0.01"
                               disabled
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="items[${itemCount}][status]" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Remove Button -->
                    <div class="flex items-end">
                        <button type="button" 
                                onclick="orderManager.removeItem(this)"
                                class="inline-flex items-center px-3 py-2 border border-transparent rounded-md text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Remove
                        </button>
                    </div>
                </div>

                <!-- Subtotal -->
                <div class="mt-2 text-right">
                    <span class="text-sm font-medium text-gray-700">Subtotal: $</span>
                    <span class="item-subtotal">0.00</span>
                    <input type="hidden" 
                           name="items[${itemCount}][subtotal]" 
                           value="0.00">
                </div>
            </div>
        `;
    }

    addNewItem() {
        const itemCount = this.container.children.length;
        this.container.insertAdjacentHTML('beforeend', this.generateItemTemplate(itemCount));
    }

    removeItem(button) {
        const item = button.closest('.order-item');
        item.remove();
        this.recalculateTotal();
        this.reindexItems();
    }

    reindexItems() {
        const items = this.container.querySelectorAll('.order-item');
        items.forEach((item, index) => {
            item.querySelectorAll('[name^="items["]').forEach(input => {
                input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
            });
        });
    }

    async handleProductChange(select) {
        if (!select?.value) return;

        const item = select.closest('.order-item');
        if (!item) return;

        await this.fetchAndUpdateVariants(select.value, item);
    }

    handleVariantChange(select) {
        if (!select) return;

        const item = select.closest('.order-item');
        const selectedOption = select.options[select.selectedIndex];
        const priceInput = item.querySelector('input[name$="[unit_price]"]');

        if (selectedOption?.dataset.price) {
            priceInput.value = parseFloat(selectedOption.dataset.price).toFixed(2);
            this.recalculateSubtotal(item);
        }
    }

    handleQuantityChange(input) {
        if (!input) return;
        const item = input.closest('.order-item');
        this.recalculateSubtotal(item);
    }

    recalculateSubtotal(item) {
        if (!item) return;

        const quantity = parseInt(item.querySelector('input[name$="[quantity]"]').value) || 0;
        const unitPrice = parseFloat(item.querySelector('input[name$="[unit_price]"]').value) || 0;
        const subtotal = (quantity * unitPrice).toFixed(2);

        item.querySelector('.item-subtotal').textContent = subtotal;
        item.querySelector('input[name$="[subtotal]"]').value = subtotal;

        this.recalculateTotal();
    }

    recalculateTotal() {
        const subtotalInputs = document.querySelectorAll('input[name$="[subtotal]"]');
        const total = Array.from(subtotalInputs)
            .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0)
            .toFixed(2);

        document.getElementById('orderTotal').textContent = total;
        document.getElementById('totalAmountInput').value = total;
    }

    async fetchAndUpdateVariants(productId, item) {
        const variantSelect = item.querySelector('select[name$="[product_variant_id]"]');
        if (!variantSelect) return;

        try {
            variantSelect.innerHTML = '<option value="">Loading...</option>';
            
            const response = await fetch(`http://localhost/Narzin/public/api/v1/products/${productId}/variants`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                credentials: 'include'
            });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            
            const variants = await response.json();

            variantSelect.innerHTML = `
                <option value="">Select Variant</option>
                ${variants.map(variant => {
                    const variantValues = variant.variant_values.map(value =>
                        `${value.variant_attribute.name_arabic || value.variant_attribute.name_german}: ${value.value}`
                    ).join(' - ');
                    return `<option value="${variant.id}" data-price="${variant.price}">${variantValues}</option>`;
                }).join('')}
            `;

        } catch (error) {
            console.error('Error fetching variants:', error);
            variantSelect.innerHTML = '<option value="">Error loading variants</option>';
        }
    }
}

// Initialize the order manager
const orderManager = new OrderManager();

// Global function to add new item (called from HTML)
function addNewItem() {
    orderManager.addNewItem();
}
        </script>

    </x-forms.form>
</x-admin-layout>

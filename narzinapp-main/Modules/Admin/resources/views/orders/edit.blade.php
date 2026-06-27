<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'order_number',
                'label' => 'Name',
                'type' => 'text',
                'width' => 'half',
                'disabled' => true,
                'value' => old('name', $order?->order_number ?? ''),
            ],
            [
                'name' => 'user_id',
                'label' => 'User',
                'type' => 'select',
                'width' => 'half',
                'disabled' => true,
                'options' => $users,
                'optionLabel' => 'name',
                'value' => old('user_id', $order?->user_id ?? ''),
            ],
            [
                'name' => 'address_id',
                'label' => 'Address',
                'type' => 'select',
                'width' => 'half',
                'options' => $addresses,
                'optionLabel' => 'address',
                'value' => old('address_id', $order?->address_id ?? ''),
            ],
            [
                'name' => 'payment_status',
                'label' => 'Payment Status',
                'type' => 'select',
                'width' => 'half',
                'options' => $statuses,
                'optionLabel' => 'name',
                'value' => old('address_id', $order?->payment_status ?? ''),
            ],
            [
                'name' => 'order_status',
                'label' => 'Order Status',
                'type' => 'select',
                'width' => 'half',
                'options' => $statuses,
                'optionLabel' => 'name',
                'value' => old('address_id', $order?->order_status ?? ''),
            ],
            [
                'name' => 'sipping_type',
                'label' => 'sipping Type',
                'type' => 'text',
                'width' => 'half',
                'value' => old('address_id', $order?->sipping_type ?? ''),
            ],
            [
                'name' => 'sipping_type',
                'label' => 'sipping Type',
                'type' => 'select',
                'options' => ['Fast', 'Normal'],
                'width' => 'half',
                'value' => old('address_id', $order?->sipping_type ?? ''),
            ],
        ];
    @endphp

    <x-forms.form routePrefix="orders" :action="route('orders.update', $order->id)" :columns="$formColumns" :data="$order ?? null"
        method="{{ isset($order) ? 'PUT' : 'POST' }}">

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
                    @foreach ($order->items as $index => $item)
                        <div class="order-item bg-gray-50 p-4 rounded-md mb-4" data-item-id="{{ $item->id }}">
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                <!-- Product Selection -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Product</label>
                                    <select name="items[{{ $index }}][product_id]"
                                        onchange="updateVariants(this)"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->name_german }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Variant Selection -->
                                @foreach ($product_variants->where('product_id', $item->product_id) as $variant)
                                @endforeach
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Variant</label>
                                    <select name="items[{{ $index }}][product_variant_id]"
                                        onchange="variantChanged(this)"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($product_variants->where('product_id', $item->product_id) as $variant)
                                            @php
                                                $variantValues = $variant->variantValues
                                                    ->map(function ($value) {
                                                        return ($value->variantAttribute->name_arabic ?:
                                                            $value->variantAttribute->name_german) .
                                                            ': ' .
                                                            $value->value;
                                                    })
                                                    ->join(' - ');
                                            @endphp
                                            <option value="{{ $variant->id }}" data-price="{{ $variant->price }}"
                                                {{ $item->product_variant_id == $variant->id ? 'selected' : '' }}>
                                                {{ $variantValues }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                    <input type="number" name="items[{{ $index }}][quantity]"
                                        value="{{ $item->quantity }}" onchange="recalculateSubtotal(this)"
                                        min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Unit Price -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                                    <input type="number" name="items[{{ $index }}][unit_price]"
                                        value="{{ $item->unit_price }}" onchange="recalculateSubtotal(this)" disabled
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select name="items[{{ $index }}][status]"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                        <option value="rejected" {{ $item->status == 'rejected' ? 'selected' : '' }}>
                                            Rejected</option>
                                    </select>
                                </div>

                                <!-- Remove Button -->
                                <div class="flex items-end">
                                    <button type="button" onclick="removeItem(this)"
                                        class="inline-flex items-center px-3 py-2 border border-transparent rounded-md text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Subtotal -->
                            <div class="mt-2 text-right">
                                <span class="text-sm font-medium text-gray-700">Subtotal: $</span>
                                <span class="item-subtotal">{{ number_format($item->subtotal, 2) }}</span>
                                <input type="hidden" name="items[{{ $index }}][subtotal]"
                                    value="{{ $item->subtotal }}">
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Order Total -->
                <div class="mt-6 text-right">
                    <span class="text-lg font-semibold text-gray-700">Total: $</span>
                    <span id="orderTotal">{{ number_format($order->total_amount, 2) }}</span>
                    <input type="hidden" name="total_amount" id="totalAmountInput" value="{{ $order->total_amount }}">
                </div>
            </div>
        </div>

        <script>
            function addNewItem() {
                const container = document.getElementById('orderItemsContainer');
                const itemCount = container.children.length;

                const template = `
        <div class="order-item bg-gray-50 p-4 rounded-md mb-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Product Selection -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Product</label>
                    <select name="items[${itemCount}][product_id]" 
                            onchange="updateVariants(this)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Product</option>
                        ${document.querySelector('select[name^="items"][name$="[product_id]"]').innerHTML}
                    </select>
                </div>

                <!-- Variant Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Variant</label>
                    <select name="items[${itemCount}][product_variant_id]" 
                            onchange="variantChanged(this)"
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
                           onchange="recalculateSubtotal(this)"
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
                            onclick="removeItem(this)"
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

                container.insertAdjacentHTML('beforeend', template);

                // Get the newly added item and update its variants based on the selected product
                const newItem = container.lastElementChild;
                const productSelect = newItem.querySelector('select[name$="[product_id]"]');
                if (productSelect.value) {
                    updateVariants(productSelect);
                }
            }

            function removeItem(button) {
                const item = button.closest('.order-item');
                item.remove();
                recalculateTotal();
                reindexItems();
            }

            function reindexItems() {
                const items = document.querySelectorAll('.order-item');
                items.forEach((item, index) => {
                    item.querySelectorAll('[name^="items["]').forEach(input => {
                        input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
                    });
                });
            }

            function recalculateSubtotal(input) {
                const item = input.closest('.order-item');
                const quantity = parseInt(item.querySelector('input[name$="[quantity]"]').value) || 0;
                const unitPrice = parseFloat(item.querySelector('input[name$="[unit_price]"]').value) || 0;

                // Calculate subtotal with proper decimal precision
                const subtotal = (quantity * unitPrice).toFixed(2);

                // Update both display and hidden input
                item.querySelector('.item-subtotal').textContent = subtotal;
                item.querySelector('input[name$="[subtotal]"]').value = subtotal;

                recalculateTotal();
            }

            function recalculateTotal() {
                // Get all subtotal hidden inputs to ensure we're using the actual values
                const subtotalInputs = document.querySelectorAll('input[name$="[subtotal]"]');

                // Sum up all subtotals with proper decimal handling
                const total = Array.from(subtotalInputs)
                    .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0)
                    .toFixed(2);

                // Update both display and hidden input for total
                document.getElementById('orderTotal').textContent = total;
                document.getElementById('totalAmountInput').value = total;
            }

            function variantChanged(select) {
                const item = select.closest('.order-item');
                const selectedOption = select.options[select.selectedIndex];
                const priceInput = item.querySelector('input[name$="[unit_price]"]');

                if (selectedOption) {
                    // Set price with proper decimal precision
                    priceInput.value = parseFloat(selectedOption.dataset.price).toFixed(2);
                    recalculateSubtotal(priceInput);
                }
            }

            async function updateVariants(productSelect) {
                const item = productSelect.closest('.order-item');
                const variantSelect = item.querySelector('select[name$="[product_variant_id]"]');
                const productId = productSelect.value;

                try {
                    const response = await fetch(`http://localhost/Narzin/public/api/v1/products/${productId}/variants`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            // If you're using Laravel Sanctum, you don't need to explicitly send the token
                            // as it uses session authentication for same-origin requests
                        },
                        credentials: 'include' // This is important for sending the session cookie
                    });

                    if (!response.ok) {
                        console.log(response);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const variants = await response.json();

                    variantSelect.innerHTML = variants.map(variant => {
                        const variantValues = variant.variant_values.map(value =>
                            `${value.variant_attribute.name_arabic || value.variant_attribute.name_german}: ${value.value}`
                        ).join(' - ');
                        return `<option value="${variant.id}" data-price="${variant.price}">${variantValues}</option>`;
                    }).join('');

                    // Update price when variant is loaded
                    const selectedVariant = variantSelect.options[variantSelect.selectedIndex];
                    if (selectedVariant) {
                        const priceInput = item.querySelector('input[name$="[unit_price]"]');
                        priceInput.value = selectedVariant.dataset.price;
                        recalculateSubtotal(priceInput);
                    }
                } catch (error) {
                    console.error('Error fetching variants:', error);
                    // Add a more user-friendly error message
                    variantSelect.innerHTML = '<option value="">Error loading variants</option>';
                }
            }
        </script>

    </x-forms.form>


</x-admin-layout>

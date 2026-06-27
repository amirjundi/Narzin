<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'vendor_id',
                'label' => 'Vendor',
                'type' => 'select',
                'options' => $vendors,
                'optionLabel' => 'store_name_in_arabic',
                'width' => 'half',
            ],
            [
                'name' => 'discount_amount',
                'label' => 'Discount Amount',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'discount_type',
                'label' => 'Discount Type',
                'type' => 'select',
                'options' => [
                    'percentage' => 'percentage',
                    'fixed' => 'fixed',
                ],
                'width' => 'half',
            ],
            [
                'name' => 'start_date',
                'label' => 'Start Date',
                'type' => 'date',
                'width' => 'half',
            ],
            [
                'name' => 'end_date',
                'label' => 'End Date',
                'type' => 'date',
                'width' => 'half',
            ],
            [
                'name' => 'usage_limit',
                'label' => 'Usage Limit',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'minimum_cart_amount',
                'label' => 'Minimum Cart Amount',
                'type' => 'text',
                'width' => 'half',
            ],
        ];
    @endphp

    <x-forms.form routePrefix="coupons" :action="route('coupons.store')" :columns="$formColumns" :data="$coupon ?? null"
        method="{{ isset($coupon) ? 'PUT' : 'POST' }}" id="couponForm">
        <input type="text" name="code" value="{{ strtoupper(Str::random(7)) }}" hidden id="">
    </x-forms.form>
</x-admin-layout>

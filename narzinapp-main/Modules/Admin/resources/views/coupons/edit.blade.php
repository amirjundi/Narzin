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
                'value' => old('name', $coupon?->vendor_id ?? '')
            ],
            [
                'name' => 'discount_amount',
                'label' => 'Discount Amount',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $coupon?->discount_amount ?? '')
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
                'value' => old('name', $coupon?->discount_type ?? '')
            ],
            [
                'name' => 'start_date',
                'label' => 'Start Date',
                'type' => 'date',
                'width' => 'half',
                'value' => old('name', $coupon?->start_date ?? '')
            ],
            [
                'name' => 'end_date',
                'label' => 'End Date',
                'type' => 'date',
                'width' => 'half',
                'value' => old('name', $coupon?->end_date ?? '')
            ],
            [
                'name' => 'usage_limit',
                'label' => 'Usage Limit',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $coupon?->usage_limit ?? '')
            ],
            [
                'name' => 'minimum_cart_amount',
                'label' => 'Minimum Cart Amount',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $coupon?->minimum_cart_amount ?? '')
            ],
        ];
    @endphp

    <x-forms.form routePrefix="coupons" :action="route('coupons.update' , $coupon->id)" :columns="$formColumns" :data="$coupon ?? null" method="{{ isset($coupon) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

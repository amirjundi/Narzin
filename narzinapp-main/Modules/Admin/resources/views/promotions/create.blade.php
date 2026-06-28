<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'type',
                'label' => 'Type',
                'type' => 'select',
                'options' => [
                    'free_shipping' => 'free_shipping',
                    'percentage' => 'percentage',
                    'fixed' => 'fixed',
                ],
                'width' => 'half',
            ],
            [
                'name' => 'value',
                'label' => 'Value (% or fixed amount)',
                'type' => 'number',
                'width' => 'half',
            ],
            [
                'name' => 'minimum_cart_amount',
                'label' => 'Minimum Cart Amount',
                'type' => 'number',
                'width' => 'half',
            ],
            [
                'name' => 'absorbed_by_vendor_percentage',
                'label' => 'Vendor Absorption %',
                'type' => 'number',
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
                'name' => 'is_active',
                'label' => 'Active',
                'type' => 'checkbox',
                'width' => 'half',
            ],
        ];
    @endphp

    <x-forms.form
        routePrefix="promotions"
        :action="route('promotions.store')"
        :columns="$formColumns"
        :data="null"
        method="POST"
    />

</x-admin-layout>

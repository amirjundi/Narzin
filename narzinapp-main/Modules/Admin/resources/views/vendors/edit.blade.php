<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'name',
                'label' => 'Name',
                'relation' => 'user',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $vendor?->name ?? '')
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'relation' => 'user',
                'type' => 'email',
                'width' => 'half',
                'value' => old('name', $vendor?->email ?? '')
],

            [
                'name' => 'store_name_in_arabic',
                'label' => 'Store Name',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $vendor?->store_name_in_arabic ?? '')
            ],
            [
                'name' => 'store_name_in_german',
                'label' => 'Store Name',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $vendor?->store_name_in_german ?? '')
            ],

            [
                'name' => 'phone',
                'label' => 'Store Phone',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $vendor?->phone ?? '')
            ],

            [
                'name' => 'status',
                'label' => 'Status',
                'options' => [
                    'Active' => 'Active',
                    'Rejected' => 'Rejected',
                    'Waiting Approve' => 'Waiting Approve',
                ],
                'type' => 'select',
                'width' => 'half',
                'value' => old('name', $vendor?->status ?? '')
            ],

            [
                'name' => 'Store_type',
                'label' => 'Store Type',
                'type' => 'select',
                'options' => [
                    'Grocery' => 'Grocery',
                    'Pharmacy' => 'Pharmacy',
                    'Restaurant' => 'Restaurant',
                ],
                'width' => 'half',
                'value' => old('name', $vendor?->Store_type ?? '')
            ],
            [
                'name' => 'password',
                'label' => 'Change Password',
                'type' => 'password',
                'width' => 'full',
            ],
            [
                'name' => 'store_logo',
                'label' => 'Store Logo',
                'type' => 'file',
                'width' => 'full',
                'value' => old('name', $vendor?->store_logo ?? '')
            ],
            [
                'name' => 'store_id',
                'label' => 'Store ID',
                'type' => 'file',
                'width' => 'full',
                'value' => old('name', $vendor?->store_id ?? '')
            ],
            [
                'name' => 'markup_percentage',
                'label' => '💰 Platform Markup Override % (Leave empty for global)',
                'type' => 'number',
                'width' => 'half',
                'value' => old('markup_percentage', $vendor?->markup_percentage ?? '')
            ],
            [
                'name' => 'exchange_rate',
                'label' => '💱 Exchange Rate Override (Leave empty for global)',
                'type' => 'number',
                'width' => 'half',
                'value' => old('exchange_rate', $vendor?->exchange_rate ?? '')
            ],
            [
                'name' => 'commission_percentage',
                'label' => 'Commission % Override (Leave empty for global default)',
                'type' => 'number',
                'width' => 'half',
                'value' => old('commission_percentage', $vendor?->commission_percentage ?? '')
            ],
            [
                'name' => 'discount_absorption_percentage',
                'label' => 'Discount Absorption % Override (Leave empty for global default)',
                'type' => 'number',
                'width' => 'half',
                'value' => old('discount_absorption_percentage', $vendor?->discount_absorption_percentage ?? '')
            ],
        ];
    @endphp

    <x-forms.form routePrefix="admins" :action="route('vendors.update' , $vendor->id)" :columns="$formColumns" :data="$vendor ?? null" method="{{ isset($vendor) ? 'PUT' : 'POST' }}">
    </x-forms.form>

</x-admin-layout>

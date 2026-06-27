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
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'width' => 'half',
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
                'width' => 'half',
            ],

            [
                'name' => 'store_name_in_arabic',
                'label' => 'Store Name',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'store_name_in_german',
                'label' => 'Store Name',
                'type' => 'text',
                'width' => 'half',
            ],

            [
                'name' => 'phone',
                'label' => 'Store Phone',
                'type' => 'text',
                'width' => 'half',
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
            ],

            [
                'name' => 'store_logo',
                'label' => 'Store Logo',
                'type' => 'file',
                'width' => 'full',
            ],
            [
                'name' => 'store_id',
                'label' => 'Store ID',
                'type' => 'file',
                'width' => 'full',
            ],
        ];
    @endphp

    <x-forms.form routePrefix="vendors"  :action="route('vendors.store')" :columns="$formColumns" :data="$vendor ?? null" method="{{ isset($vendor) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

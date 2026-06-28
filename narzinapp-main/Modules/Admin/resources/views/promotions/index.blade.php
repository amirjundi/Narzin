<x-admin-layout>
    @php
        $columns = [
            [
                'name' => 'name',
                'header' => 'Name',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'type',
                'header' => 'Type',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'value',
                'header' => 'Value',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'minimum_cart_amount',
                'header' => 'Min Cart Amount',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'absorbed_by_vendor_percentage',
                'header' => 'Vendor Absorption %',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'start_date',
                'header' => 'Start Date',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'end_date',
                'header' => 'End Date',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'is_active',
                'header' => 'Active',
                'filter' => 'agTextColumnFilter',
            ],
        ];
    @endphp

    <x-tables.table
        :data="$promotions"
        :columns="$columns"
        title="Promotions"
        titleSingular="Promotion"
        routePrefix="promotions"
        :pagination="false"
    />

</x-admin-layout>

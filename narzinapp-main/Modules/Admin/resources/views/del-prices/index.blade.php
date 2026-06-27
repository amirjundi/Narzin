<x-admin-layout>
    @php
        $columns = [
            [
                'name' => 'price',
                'header' => 'Normal Price',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'fast_price',
                'header' => 'Fast Price',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'from_days',
                'header' => 'Normal Days',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'to_days',
                'header' => 'Fast Days',
                'filter' => 'agTextColumnFilter'
            ]

        ];
    @endphp

    <x-tables.table 
        :data="$price"
        :columns="$columns"
        title="Delivery Price"
        titleSingular="Delivery Price"
        routePrefix="delivery-prices"
        :pagination="false"
    />
    
</x-admin-layout>
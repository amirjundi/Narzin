<x-admin-layout>
    @php
        $columns = [
            [
                'name' => 'code',
                'header' => 'Code',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'vendor.store_name_in_arabic',
                'header' => 'Vendor',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'discount_amount',
                'header' => 'Discount Amount',
                'filter' => 'agTextColumnFilter'
            ],

            [
                'name' => 'discount_type',
                'header' => 'Discount Type',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'start_date',
                'header' => 'Start Date',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'end_date',
                'header' => 'End Date',
                'filter' => 'agTextColumnFilter'
            ],

            [
                'name' => 'usage_limit',
                'header' => 'Usage Limit',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'minimum_cart_amount',
                'header' => 'Minimum Cart Amount',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'used',
                'header' => 'Used',
                'filter' => 'agTextColumnFilter'
            ],
        ];
    @endphp

    <x-tables.table 
        :data="$coupons"
        :columns="$columns"
        title="Coupons"
        titleSingular="Coupon"
        routePrefix="coupons"
        :pagination="false"
    />
    
</x-admin-layout>
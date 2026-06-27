<x-admin-layout>
    @php
        $columns = [
            [
                'name' => 'user.name',
                'header' => 'Name',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'user.email',
                'header' => 'Email',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'status',
                'header' => 'Status',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'store_name_in_arabic',
                'header' => 'Store Name',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'phone',
                'header' => 'Store Phone',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'store_logo',
                'header' => 'Store logo',
                'filter' => 'agTextColumnFilter',
                'type' => 'image'  
            ],
            [
                'name' => 'store_id',
                'header' => 'Store ID',
                'filter' => 'agTextColumnFilter',
                'type' => 'file'  
            ],
        ];
    @endphp

    <x-tables.table 
        :data="$vendors"
        :columns="$columns"
        title="Vendors"
        titleSingular="Vendor"
        routePrefix="vendors"
        :pagination="false"
    />
    
</x-admin-layout>
<x-admin-layout>
    @php
        $columns = [
            [
                'name' => 'image',
                'header' => 'Image',
                'type' => 'image',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'is_mobile',
                'header' => 'Is Mobile',
                'filter' => 'agTextColumnFilter'
            ],
        ];
    @endphp

    <x-tables.table 
        :data="$banners"
        :columns="$columns"
        title="Banners"
        titleSingular="Banner"
        routePrefix="banners"
        :pagination="false"
    />
    
</x-admin-layout>
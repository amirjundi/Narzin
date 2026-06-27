<x-admin-layout>
    @php
        $columns = [
            [
                'name' => 'name_arabic',
                'header' => 'Name in Arabic',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'name_german',
                'header' => 'Name in German',
                'filter' => 'agTextColumnFilter'
            ],

            [
                'name' => 'image',
                'header' => 'Image',
                'type' => 'image',
                'filter' => 'agTextColumnFilter'
            ],
        ];
    @endphp

    <x-tables.table 
        :data="$categories"
        :columns="$columns"
        title="Categories"
        titleSingular="Category"
        routePrefix="categories"
        :pagination="false"
    />
    
</x-admin-layout>
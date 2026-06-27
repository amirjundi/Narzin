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
                'name' => 'parent.name_german',
                'header' => 'Parent Category',
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
        :data="$subCategories"
        :columns="$columns"
        title="Sub Categories"
        titleSingular="Sub Category"
        routePrefix="sub-categories"
        :pagination="false"
    />
    
</x-admin-layout>
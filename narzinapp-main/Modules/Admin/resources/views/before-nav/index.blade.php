<x-admin-layout>
    @php
        $columns = [
            [
                'name' => 'text',
                'header' => 'Text',
                'type' => 'text',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'start_date',
                'header' => 'Start Date',
                'type' => 'date',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'end_date',
                'header' => 'End Date',
                'type' => 'date',
                'filter' => 'agTextColumnFilter'
            ],
        ];
    @endphp

    <x-tables.table 
        :data="$texts"
        :columns="$columns"
        title="Before Nav"
        titleSingular="Texts Before Nav"
        routePrefix="before-nav"
        :pagination="false"
    />
    
</x-admin-layout>
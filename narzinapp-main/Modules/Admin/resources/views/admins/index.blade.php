<x-admin-layout>
    @php
        $columns = [
            [
                'name' => 'name',
                'header' => 'Name',
                'filter' => 'agTextColumnFilter'
            ],
            [
                'name' => 'email',
                'header' => 'Email',
                'filter' => 'agTextColumnFilter'
            ],

            [
                'name' => 'admin.is_active',
                'header' => 'Is Active Admin',
                'filter' => 'agTextColumnFilter'
            ],
        ];
    @endphp

    <x-tables.table 
        :data="$admins"
        :columns="$columns"
        title="Admins"
        titleSingular="Admin"
        routePrefix="admins"
        :pagination="false"
    />
    
</x-admin-layout>
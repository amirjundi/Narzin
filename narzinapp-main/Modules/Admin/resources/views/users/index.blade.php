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
            ]
        ];
    @endphp

    <x-tables.table 
        :data="$users"
        :columns="$columns"
        title="Users"
        titleSingular="User"
        routePrefix="users"
        :pagination="false"
    />
    
</x-admin-layout>
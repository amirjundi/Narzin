<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'text',
                'label' => 'Text',
                'type' => 'text',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'start_date',
                'label' => 'Start Date',
                'type' => 'date',
                'filter' => 'agTextColumnFilter',
            ],
            [
                'name' => 'end_date',
                'label' => 'End Date',
                'type' => 'date',
                'filter' => 'agTextColumnFilter',
            ],
        ];
    @endphp

    <x-forms.form routePrefix="before-nav"  :action="route('before-nav.store')" :columns="$formColumns" :data="$admin ?? null" method="{{ isset($admin) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

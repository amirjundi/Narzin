<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'text',
                'label' => 'Text',
                'type' => 'text',
                'filter' => 'agTextColumnFilter',
                'value' => old('name', $beforeNav?->text ?? '')
            ],
            [
                'name' => 'start_date',
                'label' => 'Start Date',
                'type' => 'date',
                'filter' => 'agTextColumnFilter',
                'value' => old('name', $beforeNav?->start_date ?? '')
            ],
            [
                'name' => 'end_date',
                'label' => 'End Date',
                'type' => 'date',
                'filter' => 'agTextColumnFilter',
                'value' => old('name', $beforeNav?->end_date ?? '')
            ],
        ];
    @endphp


    <x-forms.form routePrefix="before-nav" :action="route('before-nav.update', $beforeNav->id)" :columns="$formColumns" :data="$beforeNav ?? null"
        method="{{ isset($admin) ? 'PUT' : 'PUT' }}" />


</x-admin-layout>

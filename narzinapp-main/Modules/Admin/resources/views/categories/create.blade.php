<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'name_arabic',
                'label' => 'Name in Arabic',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'name_german',
                'label' => 'Name in German',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'image',
                'label' => 'Image',
                'type' => 'file',
                'width' => 'full',
            ]

        ];
    @endphp

    <x-forms.form routePrefix="categories"  :action="route('categories.store')" :columns="$formColumns" :data="$category ?? null" method="{{ isset($category) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

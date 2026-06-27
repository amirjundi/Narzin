<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'name_arabic',
                'label' => 'Name in Arabic',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $category?->name_arabic ?? '')
            ],
            [
                'name' => 'name_german',
                'label' => 'Name in German',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $category?->name_german ?? '')
            ],
            [
                'name' => 'image',
                'label' => 'Image',
                'type' => 'file',
                'width' => 'full',
                'value' => old('name', $category?->image ?? '')
            ]

        ];
    @endphp

    <x-forms.form routePrefix="categories" :action="route('categories.update' , $category->id)" :columns="$formColumns" :data="$category ?? null" method="{{ isset($category) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

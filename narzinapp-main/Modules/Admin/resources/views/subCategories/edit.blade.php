<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'name_arabic',
                'label' => 'Name in Arabic',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $subCategory?->name_arabic ?? ''),
            ],
            [
                'name' => 'name_german',
                'label' => 'Name in German',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $subCategory?->name_german ?? ''),
            ],
            [
                'name' => 'image',
                'label' => 'Image',
                'type' => 'file',
                'width' => 'full',
                'value' => old('name', $subCategory?->image ?? ''),
            ],
            [
                'name' => 'parent_id',
                'label' => 'Parent Category',
                'type' => 'select',
                'options' => $categories,
                'optionLabel' => 'name_german',
                'width' => 'half',
                'value' => old('name', $subCategory?->parent_id ?? ''),
            ],
        ];
    @endphp

    <x-forms.form routePrefix="sub-categories" :action="route('sub-categories.update', $subCategory->id)" :columns="$formColumns" :data="$subCategory ?? null"
        method="{{ isset($subCategory) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

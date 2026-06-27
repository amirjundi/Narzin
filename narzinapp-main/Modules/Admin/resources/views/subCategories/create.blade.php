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
],
            [
                'name' => 'parent_id',
                'label' => 'Parent Category',
                'type' => 'select',
                'options' => $subCategories,
                'optionLabel' => 'name_german',
                'width' => 'half',
            ],
        ];
    @endphp

    <x-forms.form routePrefix="sub-categories"  :action="route('categories.store')" :columns="$formColumns" :data="$subCategory ?? null" method="{{ isset($subCategory) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

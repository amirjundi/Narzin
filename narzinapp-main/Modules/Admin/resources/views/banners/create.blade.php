<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'image',
                'label' => 'Photo',
                'type' => 'file',
                'width' => 'half',
            ],
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'description',
                'label' => 'Description',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'is_mobile',
                'label' => 'Is Active',
                'type' => 'checkbox',
                'width' => 'half',
            ],
        ];
    @endphp

    <x-forms.form routePrefix="banners"  :action="route('banners.store')" :columns="$formColumns" :data="$admin ?? null" method="{{ isset($admin) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

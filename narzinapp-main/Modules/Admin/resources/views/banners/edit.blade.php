<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'image',
                'label' => 'Photo',
                'type' => 'file',
                'width' => 'half',
                'value' => old('name', $banner?->image ?? '')
            ],
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $banner?->title ?? '')
            ],
            [
                'name' => 'description',
                'label' => 'Description',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $banner?->description ?? '')
            ],
            [
                'name' => 'is_mobile',
                'label' => 'Is Active',
                'type' => 'checkbox',
                'width' => 'half',
                'value' => old('name', $banner?->is_mobile ?? '')
            ],
        ];
    @endphp


    <x-forms.form routePrefix="banners" :action="route('banners.update' , $banner->id)" :columns="$formColumns" :data="$banner ?? null" method="{{ isset($admin) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

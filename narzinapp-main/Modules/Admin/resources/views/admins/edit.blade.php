<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $admin?->name ?? '')
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'width' => 'half',
                'value' => old('name', $admin?->email ?? '')

            ],
            [
                'name' => 'password',
                'label' => 'Change Password',
                'type' => 'password',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'is_active',
                'label' => 'Is Active',
                'type' => 'checkbox',
                'width' => 'half',
                'relation' => 'admin',
                'value' => old('name', $admin?->is_active ?? '')
            ],

        ];
    @endphp

    <x-forms.form routePrefix="admins" :action="route('admins.update' , $admin->id)" :columns="$formColumns" :data="$admin ?? null" method="{{ isset($admin) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

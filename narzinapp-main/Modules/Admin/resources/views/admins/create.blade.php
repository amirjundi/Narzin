<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'width' => 'half',
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
                'width' => 'half',
            ],

            [
                'name' => 'is_active',
                'label' => 'Is Active',
                'type' => 'checkbox',
                'width' => 'half',
            ],
        ];
    @endphp

    <x-forms.form routePrefix="admins"  :action="route('admins.store')" :columns="$formColumns" :data="$admin ?? null" method="{{ isset($admin) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

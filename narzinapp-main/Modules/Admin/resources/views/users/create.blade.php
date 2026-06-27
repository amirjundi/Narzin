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
        ];
    @endphp

    <x-forms.form routePrefix="users"  :action="route('users.store')" :columns="$formColumns" :data="$user ?? null" method="{{ isset($user) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

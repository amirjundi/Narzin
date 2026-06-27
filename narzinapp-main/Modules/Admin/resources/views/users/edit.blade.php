<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $user?->name ?? '')
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'width' => 'half',
                'value' => old('name', $user?->email ?? '')

            ],
            [
                'name' => 'password',
                'label' => 'Change Password',
                'type' => 'password',
                'type' => 'text',
                'width' => 'half',
            ],

        ];
    @endphp

    <x-forms.form routePrefix="users" :action="route('users.update' , $user->id)" :columns="$formColumns" :data="$user ?? null" method="{{ isset($user) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

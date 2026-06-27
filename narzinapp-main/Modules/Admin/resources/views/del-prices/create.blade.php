<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'price',
                'label' => 'Normal Price',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'fast_price',
                'label' => 'Fast Price',
                'type' => 'text',
                'width' => 'half',
            ],
            [
                'name' => 'from_days',
                'label' => 'Normal Days',
                'type' => 'text',
                'width' => 'half',
],
            [
                'name' => 'to_days',
                'label' => 'Fast Days',
                'type' => 'text',
                'width' => 'half',
            ],
          
        ];
    @endphp

    <x-forms.form routePrefix="delivery-prices" :action="route('delivery-prices.store')" :columns="$formColumns" :data="$price ?? null"
        method="{{ isset($price) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

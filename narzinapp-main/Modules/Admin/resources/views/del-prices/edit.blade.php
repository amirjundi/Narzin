<x-admin-layout>
    @php
        $formColumns = [
            [
                'name' => 'price',
                'label' => 'Normal Price',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $price?->price ?? '')
            ],
            [
                'name' => 'fast_price',
                'label' => 'Fast Price',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $price?->fast_price ?? '')

            ],
            [
                'name' => 'from_days',
                'label' => 'Normal Days',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $price?->from_days ?? '')
            ],
            [
                'name' => 'to_days',
                'label' => 'Fast Days',
                'type' => 'text',
                'width' => 'half',
                'value' => old('name', $price?->to_days ?? '')
            ],


        ];
    @endphp

    <x-forms.form routePrefix="delivery-prices" :action="route('delivery-prices.update' , $price->id)" :columns="$formColumns" :data="$price ?? null" method="{{ isset($price) ? 'PUT' : 'POST' }}" />


</x-admin-layout>

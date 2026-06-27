@props(['columns', 'routePrefix' => '', 'data', 'relations' => []])

<div class="space-y-4">
    <div class="grid grid-cols-12 gap-4">
        @foreach ($columns as $column)
            @php
                if (isset($column['relation'])) {
                    $column['value'] = $data->{$column['relation']}->{$column['name']};
                } else {
                    $column['value'] = $data->{$column['name']};
                }

                $spanClass = match ($column['width'] ?? 'full') {
                    'half' => 'col-span-6',
                    'third' => 'col-span-4',
                    'quarter' => 'col-span-3',
                    default => 'col-span-12',
                };
            @endphp

            <div class="{{ $spanClass }} px-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">{{ $column['label'] }}</label>
                    @switch($column['type'])
                        @case('file')
                            @if ($data->{$column['name']})
                                @if (in_array(pathinfo($column['value'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                    <img src="{{ asset('storage/' . $column['value']) }}" 
                                         alt="{{ $column['label'] }}" 
                                         class="max-w-full h-auto rounded">
                                @else
                                    <a href="{{ asset('storage/' . $column['value']) }}" 
                                       class="text-primary hover:underline"
                                       download>
                                        Download File
                                    </a>
                                @endif
                            @endif
                        @break


                        @case('boolean')
                       
                            <div class="w-full rounded border px-3 py-2 {{ $column['value'] ? 'bg-green-50' : 'bg-red-50' }}">
                                {{ $column['value'] ? 'فعال' : 'غير فعال' }}
                            </div>
                    @break

                        @case('checkbox')
                            <div class="w-full rounded border px-3 py-2 bg-gray-50">
                                {{ $column['value'] ? 'Yes' : 'No' }}
                            </div>
                        @break

                        @case('select')
                            <div class="w-full rounded border px-3 py-2 bg-gray-50">
                                @if (isset($column['options']))
                                    @if (isset($column['optionLabel']))
                                    {!! $column['options']->where('id', $column['value'])->first()->{$column['optionLabel']} !!}

                                    @else
                                        {{ $column['options'][$column['value']] }}
                                    @endif
                                @endif
                            </div>
                        @break

                        @default
                            <div class="w-full rounded border px-3 py-2 bg-gray-50">
                                {{ $column['value'] }}
                            </div>
                    @endswitch
                </div>
            </div>
        @endforeach

        {{ $slot ?? '' }}
    </div>

    <div class="flex justify-end gap-2 px-4 mt-6">
        <a href="{{ route($routePrefix . '.edit', $data->id) }}"
            class="bg-primary text-white px-4 py-2 rounded hover:bg-primary-dark transition">
            تعديل
        </a>
        <a href="{{ route($routePrefix . '.index') }}"
            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
            رجوع
        </a>
    </div>



      {{-- Relations Section --}}
      @if(isset($relations) && count($relations) > 0)
      @foreach($relations as $relation)
          @if($data->{$relation['name']}->count() > 0)
              <div class="mt-8">
                  <h3 class="text-lg font-semibold mb-4">{{ $relation['label'] }}</h3>
                  <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-gray-200">
                          <thead class="bg-gray-50">
                              <tr>
                                  @foreach($relation['columns'] as $column)
                                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                          {{ $column['label'] }}
                                      </th>
                                  @endforeach
                                  @if(isset($relation['actions']) && $relation['actions'])
                                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                          الإجراءات
                                      </th>
                                  @endif
                              </tr>
                          </thead>
                          <tbody class="bg-white divide-y divide-gray-200">
                              @foreach($data->{$relation['name']} as $item)
                                  <tr>
                                      @foreach($relation['columns'] as $column)
                                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                              @if(isset($column['type']) && $column['type'] === 'date')
                                                  {{ \Carbon\Carbon::parse($item->{$column['name']})->format($column['format'] ?? 'Y-m-d') }}
                                              @elseif(isset($column['type']) && $column['type'] === 'boolean')
                                                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->{$column['name']} ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                      {{ $item->{$column['name']} ? 'نعم' : 'لا' }}
                                                  </span>
                                              @elseif(isset($column['type']) && $column['type'] === 'file')
                                                  @if($item->{$column['name']})
                                                      <a href="{{ asset('storage/' . $item->{$column['name']}) }}" 
                                                         class="text-primary hover:underline"
                                                         target="_blank">
                                                          عرض الملف
                                                      </a>
                                                  @endif
                                              @else
                                                  {{ $item->{$column['name']} }}
                                              @endif
                                          </td>
                                      @endforeach
                                      @if(isset($relation['actions']) && $relation['actions'])
                                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                              <div class="flex gap-2">
                                                  @foreach($relation['actions'] as $action)
                                                      <a href="{{ route($action['route'], $item->id) }}"
                                                         class="{{ $action['class'] ?? 'text-primary hover:underline' }}">
                                                          {{ $action['label'] }}
                                                      </a>
                                                  @endforeach
                                              </div>
                                          </td>
                                      @endif
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                  </div>
              </div>
          @endif
      @endforeach
  @endif

</div>
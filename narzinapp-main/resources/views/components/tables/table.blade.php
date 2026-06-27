@if ($title)
    <div class="flex justify-between items-center">
        <x-admin.title :title="$title" />
        <a href="{{ config('app.url') }}/{{ $routePrefix }}/create" class="btn bg-[#225E8A] text-white">new
            {{ $titleSingular }}</a>
    </div>
@endif
<x-alerts />

<div id="myGrid" class="ag-theme-quartz" style="height: {{ $height ?? '500px' }}"></div>

<script>
    const appUrl = '{{ config('app.url') }}';

    // Function to process columns and add actions
    function processColumns(columns, enableActions, routePrefix) {
        let processedColumns = columns.map(col => {
            let columnDef = {
                field: col.name,
                headerName: col.header,
                filter: col.filter || 'agTextColumnFilter',
                sortable: true
            };

            if (col.type === 'image') {
                columnDef.cellRenderer = params => {
                    if (!params.value) return '';
                    const imagePath = `${appUrl}/storage/${params.value}`;
                    return `<img src="${imagePath}" alt="Image" class="h-10 w-10 object-cover rounded"/>`;
                };
            } else if (col.type === 'file') {
                columnDef.cellRenderer = params => {
                    if (!params.value) return '';
                    const filePath = `${appUrl}/storage/${params.value}`;
                    return `<a href="${filePath}" download class="text-blue-600 hover:text-blue-900">
                <i class="fas fa-download"></i> Download
            </a>`;
                };
            }

            return columnDef;
        });

        if (enableActions) {
            processedColumns.push({
                headerName: 'actions',
                filter: false,
                sortable: false,
                cellRenderer: params => {
                    return `
                        <div class="flex gap-2">
                            <a href="${appUrl}/${routePrefix}/${params.data.id}/edit" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i>
                            </a>

                            <a href="${appUrl}/${routePrefix}/${params.data.id}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <form action="${appUrl}/${routePrefix}/${params.data.id}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('sure you want to delete this item ')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    `;
                }
            });
        }

        // Add checkbox selection column (updated for v32.2+)
        processedColumns.unshift({
            headerName: '',
            filter: false,
            resizable: false,
            pinned: 'left',
            lockPosition: true,
            width: 50,
            minWidth: 50,
            maxWidth: 50
        });
        
        return processedColumns;
    }

    var gridOptions = {
        rowData: @json($pagination ? $data->items() : $data),
        columnDefs: processColumns(@json($columns), {{ json_encode($enableActions ?? true) }}, '{{ $routePrefix }}'),
        
        // Updated row selection configuration for v32.2+
        rowSelection: {
            mode: 'multiRow',
            checkboxes: true,
            headerCheckbox: true,
            enableClickSelection: false
        },
        
        // Remove autoSizeStrategy to fix flex conflict
        // autoSizeStrategy: {
        //     type: 'fitGridWidth'
        // },
        
        defaultColDef: {
            resizable: true,
            flex: 1,
            minWidth: 100,
            sortable: true,
            filter: true
        },
    };

    var gridDiv = document.querySelector('#myGrid');
    agGrid.createGrid(gridDiv, gridOptions);
</script>

@if ($pagination)
    <div class="mt-4">
        {{ $data->links() }}
    </div>
@endif
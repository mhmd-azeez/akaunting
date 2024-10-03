<div id="widget-{{ $class->model->id }}" class="{{ $class->model->settings->width }} my-8">
    @include($class->views['header'], ['header_class' => ''])

    <h4>This is a very cool widget</h4>

    <x-table>
        <x-table.thead>
            <x-table.tr class="flex items-center px-1">
                <x-table.th class="w-4/12">
                    {{ trans('invoices.due_date') }}
                </x-table.th>

                <x-table.th class="w-4/12">
                    {{ trans_choice('general.customers', 1) }}
                </x-table.th>

                <x-table.th class="w-4/12">
                    {{ trans('general.amount') }}
                </x-table.th>
            </x-table.tr>
        </x-table.thead>

        <x-table.tbody>
            @if (!empty($invoices))
                @foreach($invoices as $invoice)
                    <x-table.tr class="relative flex items-center border-b hover:bg-gray-100 px-1 group transition-all">
                        <x-table.td class="w-5/12 truncate">
                            <x-date :date="$invoice->due_at" />
                        </x-table.td>

                        <x-table.td class="w-5/12 truncate">
                            {{ $invoice->contact->name }}
                        </x-table.td>

                        <x-table.td class="w-5/12 relative">
                            <x-money :amount="$invoice->amount" :currency="$invoice->currency_code" convert />
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            @else
                <x-table.tr class="border-top-1">
                    <x-table.td colspan="3">
                        <div class="text-muted nr-py" id="datatable-basic_info" role="status" aria-live="polite">
                            {{ trans('general.no_records') }}
                        </div>
                    </x-table.td>
                </x-table.tr>
            @endif
        </x-table.tbody>
    </x-table>
</div>

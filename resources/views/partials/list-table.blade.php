<bulk-actions class="card p-0 block" :total-rows="{{ $combinations->total() }}"
              :ids="@json($combinations->modelKeys())"
              :has-more-pages="{{ $combinations->hasPages() ? 'true' : 'false' }}"  v-slot="bulk">
    <table>
        @include('admin::partials.bulk-header', ['list' => $list])
        <tr class="header" v-else>
            <th>
                @if($sortBy === 'label-az')
                    <a href="{{ route('admin.content.combinations', array_merge(request()->all(), ['sort' => 'label-za', 'page' => null])) }}">Label<span class="no-underline ml-2">@include('admin::icons.sort-az')</span></a>
                @elseif($sortBy === 'label-za')
                    <a href="{{ route('admin.content.combinations', array_merge(request()->all(), ['sort' => null, 'page' => null])) }}">Label<span class="no-underline ml-2">@include('admin::icons.sort-za')</span></a>
                @else
                    <a href="{{ route('admin.content.combinations', array_merge(request()->all(), ['sort' => 'label-az', 'page' => null])) }}">Label</a>
                @endif
            </th>
            <th></th>
        </tr>
        @forelse($combinations as $combination)
            <tr>
                <td>
                    <a href="{{ route('admin.modules.merchandising.listings', array_merge(request()->all(), ['combination' => $combination])) }}">{{ $combination->label }}</a>
                </td>
                <td>
                    <div class="flex items-center justify-end">
                        <a class="mr-2" href="{{ route('admin.modules.merchandising.listings', array_merge(request()->all(), ['combination' => $combination])) }}">@include('admin::icons.manage')</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No combinations</td>
            </tr>
        @endforelse
    </table>
</bulk-actions>

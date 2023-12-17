<div class="panelV2" x-data="{ show: false }">
    <h2 class="panel__heading" style="cursor: pointer;" @click="show = !show">
        <i class="{{ config("other.font-awesome") }} fa-clipboard-list"></i> Audits
        <i class="{{ config("other.font-awesome") }} fa-plus-circle fa-pull-right" x-show="!show"></i>
        <i class="{{ config("other.font-awesome") }} fa-minus-circle fa-pull-right" x-show="show" x-cloak></i>
    </h2>
    <div class="data-table-wrapper" x-show="show" x-cloak>
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>{{ __('common.action') }}</th>
                    <th>Date</th>
                    <th>Modifications</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits->load(['user.group']) as $audit)
                    @php $values = json_decode($audit->record, true) @endphp
                    <tr>
                        <td>
                            <x-user_tag :user="$audit->user" :anon="false" />
                        </td>
                        <td>{{ $audit->action }}</td>
                        <td>
                            <time datetime="{{ $audit->created_at }}" title="{{ $audit->created_at }}">
                                {{ $audit->created_at }} ({{ $audit->created_at->diffForHumans() }})
                            </time>
                        </td>
                        <td>
                            <ul>
                                @foreach ($values as $key => $value)
                                    <li style="word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;">
                                        {{ $key }}:
                                        @if (is_array($value['old']))
                                            @json($value['old'])
                                        @else
                                            {{ $value['old'] ?? 'null' }}
                                        @endif
                                        &rarr;
                                        @if (is_array($value['new']))
                                            @json($value['new'])
                                        @else
                                            {{ $value['new'] ?? 'null' }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<menu class="torrent__buttons form__group--short-horizontal">
    <li class="form__group form__group--short-horizontal">
        @if ($fileExists = file_exists(public_path().'/files/torrents/'.$torrent->file_name))
            @if (config('torrent.download_check_page') == 1)
                <a
                    class="form__button form__button--filled form__button--centered"
                    href="{{ route('download_check', ['id' => $torrent->id]) }}" role="button"
                >
                    <i class='{{ config("other.font-awesome") }} fa-download'></i> {{ __('common.download') }}
                </a>
            @else
                <a
                    class="form__button form__button--filled form__button--centered"
                    href="{{ route('download', ['id' => $torrent->id]) }}"
                >
                    <i class='{{ config("other.font-awesome") }} fa-download'></i> {{ __('common.download') }}
                </a>
            @endif
        @else
            <a
                href="magnet:?dn={{ $torrent->name }}&xt=urn:btih:{{ bin2hex($torrent->info_hash) }}&as={{ route('torrent.download.rsskey', ['id' => $torrent->id, 'rsskey' => $user->rsskey ]) }}&tr={{ route('announce', ['passkey' => $user->passkey]) }}&xl={{ $torrent->size }}"
                class="form__button form__button--filled form__button--centered"
            >
                <i class='{{ config("other.font-awesome") }} fa-magnet'></i> {{ __('common.magnet') }}
            </a>
        @endif
    </li>
    @if ($fileExists)
        @if ($torrent->free !== 100 && config('other.freeleech') == false && ! $personal_freeleech && $user->group->is_freeleech == 0 && ! $torrent->freeleechToken_exists)
            <li class="form__group form__group--short-horizontal">
                <form
                    action="{{ route('freeleech_token', ['id' => $torrent->id]) }}"
                    method="POST"
                    style="display: contents;"
                    x-data
                >
                    @csrf
                    <button
                        class="form__button form__button--outlined form__button--centered"
                        title="{{ __('torrent.fl-tokens-left', ['tokens' => $user->fl_tokens]) }}!"
                        x-on:click.prevent="
                            Swal.fire({
                                title: 'Are you sure?',
                                text: 'This will use one of your Freeleech Tokens!',
                                icon: 'warning',
                                showConfirmButton: true,
                                showCloseButton: true,
                            }).then((result) => {
                                if (result.isConfirmed && {{ $torrent->seeders }} == 0) {
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: 'This torrent has 0 seeders!',
                                        icon: 'warning',
                                        showConfirmButton: true,
                                        showCancelButton: true,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $root.submit();
                                        }
                                    });
                                } else if (result.isConfirmed) {
                                    $root.submit();
                                }
                            });
                        "
                    >
                        {{ __('torrent.use-fl-token') }}
                    </button>
                </form>
            </li>
        @endif
    @endif
    <li class="form__group form__group--short-horizontal">
        @livewire('thank-button', ['torrent' => $torrent->id])
    </li>
    @if ($torrent->nfo)
        <li x-data class="form__group form__group--short-horizontal">
            <button class="form__button form__button--outlined form__button--centered" x-on:click.stop="$refs.dialog.showModal()">
                <i class='{{ config("other.font-awesome") }} fa-info-circle'></i> NFO
            </button>
            <dialog class="dialog dialog--auto-width" x-ref="dialog">
                <h4 class="dialog__heading">
                    NFO
                </h4>
                <div class="dialog__form" x-on:click.outside="$refs.dialog.close()">
                    <div class="bbcode-rendered" style="text-align: left">
                        <pre style="width: max-content"><code style="white-space: pre;">{{ iconv('cp437', 'utf8', $torrent->nfo) }}</code></pre>
                    </div>
                </div>
            </dialog>
        </li>
    @endif
    <li x-data class="form__group form__group--short-horizontal">
        <button class="form__button form__button--outlined form__button--centered" x-on:click.stop="$refs.dialog.showModal()">
            <i class='{{ config("other.font-awesome") }} fa-coins'></i> {{ __('torrent.leave-tip') }}
        </button>
        <dialog class="dialog" x-ref="dialog">
            <h4 class="dialog__heading">
                {{ __('torrent.tip-jar') }}
            </h4>
            <form
                class="dialog__form"
                method="POST"
                action="{{ route('users.tips.store', ['user' => auth()->user()]) }}"
                x-on:click.outside="$refs.dialog.close()"
            >
                @csrf
                <input type="hidden" name="torrent" value="{{ $torrent->id }}">
                <div>
                    {!! __('torrent.torrent-tips', ['total' => $total_tips, 'user' => $user_tips]) !!}.
                    <span>({{ __('torrent.torrent-tips-desc') }})</span>
                </div>
                <div class="form__group">
                    <input
                        id="tip"
                        class="form__text"
                        list="torrent_quick_tips"
                        name="tip"
                        placeholder=" "
                        type="text"
                        pattern="[0-9]*"
                        inputmode="numeric"
                    >
                    <label class="form__label form__label--floating" for="tip">
                        {{ __('torrent.define-tip-amount') }}
                    </label>
                    <datalist id="torrent_quick_tips">
                        <option value="1000">
                        <option value="2000">
                        <option value="5000">
                        <option value="10000">
                        <option value="20000">
                        <option value="50000">
                        <option value="100000">
                    </datalist>
                </div>
                <div class="form__group">
                    <button class="form__button form__button--filled">{{ __('torrent.leave-tip') }}</button>
                </div>
            </form>
        </dialog>
    </li>
    <li x-data="{ tab: 'hierarchy' }" class="form__group form__group--short-horizontal">
        <button
            class="form__button form__button--outlined form__button--centered"
            title="{{ __('common.edit') }}"
            x-on:click.stop="$refs.dialog.showModal()"
        >
            <i class='{{ config("other.font-awesome") }} fa-file'></i> {{ __('torrent.show-files') }}
        </button>
        <dialog class="dialog dialog--auto-width" x-ref="dialog">
            <header class="dialog__header">
                <h4 class="dialog__heading">
                    {{ __('common.files') }}
                </h4>
                <div class="dialog__actions">
                    <div class="dialog__action">
                        {{ __('torrent.info-hash') }}:
                        {{ bin2hex($torrent->info_hash) }}
                    </div>
                </div>
            </header>
            <div x-on:click.outside="$refs.dialog.close()">
                <menu class="panel__tabs">
                    <li
                        class="panel__tab"
                        role="tab"
                        x-bind:class="tab === 'hierarchy' && 'panel__tab--active'"
                        x-on:click="tab = 'hierarchy'"
                    >
                        Hierarchy
                    </li>
                    <li
                        class="panel__tab"
                        role="tab"
                        x-bind:class="tab === 'list' && 'panel__tab--active'"
                        x-on:click="tab = 'list'"
                    >
                        List
                    </li>
                </menu>
                <div class="dialog__form" x-show="tab === 'hierarchy'">
                    @if ($torrent->folder !== null)
                        <span style="display: grid; grid-template-areas: 'icon folder count . size'; grid-template-columns: 24px auto auto 1fr auto; align-items: center; padding-bottom: 8px;">
                            <i
                                class="{{ config('other.font-awesome') }} fa-folder"
                                style="grid-area: icon; padding-right: 4px"
                            ></i>
                            <span style="padding-right: 4px; word-break: break-all">
                                {{ $torrent->folder  }}
                            </span>
                            <span style="grid-area: count; padding-right: 4px;">
                                ({{ $torrent->files()->count() }})
                            </span>
                            <span
                                class="text-info"
                                style="grid-area: size; white-space: nowrap; text-align: right;"
                                title="{{ $torrent->size }}&nbsp;B"
                            >
                                {{ App\Helpers\StringHelper::formatBytes($torrent->size, 2) }}
                            </span>
                        </span>
                    @endif
                    @foreach ($files = $torrent->files->sortBy('name')->values()->sortBy(fn ($f) => dirname($f->name)."/~~~", SORT_NATURAL)->values() as $file)
                        @php $prevNodes = explode("/", $files[$loop->index - 1]->name ?? " ") @endphp
                        @foreach ($nodes = explode("/", $file->name) as $node)
                            @if (($prevNodes[$loop->index] ?? "") != $node)
                                @for ($depth = count($prevNodes); $depth > $loop->index; $depth--)
                                    </details>
                                @endfor
                                @for ($depth = $loop->index; $depth < $loop->count; $depth++)
                                    <details style="@if ($depth != 0) margin-left: 20px; @endif">
                                        <summary style="padding: 8px; @if ($depth != $loop->count - 1) cursor: pointer; @endif">
                                            <span style="display: grid; grid-template-areas: 'icon1 icon2 folder count . size'; grid-template-columns: 24px 24px auto auto 1fr auto;">
                                                @if ($depth == $loop->count - 1)
                                                    <i style="grid-area: icon1"></i>
                                                    <i
                                                        class="{{ config('other.font-awesome') }} fa-file"
                                                        style="grid-area: icon2; padding-right: 4px"
                                                    ></i>
                                                    <span style="padding-right: 4px; word-break: break-all">
                                                        {{ $nodes[$depth] }}
                                                    </span>
                                                    <span
                                                        style="grid-area: size; white-space: nowrap; text-align: right;"
                                                        title="{{ $file->size }}&nbsp;B"
                                                    >
                                                        {{ $file->getSize() }}
                                                    </span>
                                                @else
                                                    <i
                                                        class="{{ config('other.font-awesome') }} fa-caret-right"
                                                        style="grid-area: icon1;"
                                                    ></i>
                                                    <i
                                                        class="{{ config('other.font-awesome') }} fa-folder"
                                                        style="grid-area: icon2; padding-right: 4px;"
                                                    ></i>
                                                    <span style="padding-right: 4px">
                                                        {{ $nodes[$depth] }}
                                                    </span>
                                                    @php
                                                        $filteredFiles = $files->filter(fn ($value) =>
                                                            str_starts_with(
                                                                $value->name,
                                                                implode("/", array_slice($nodes, 0, $depth + 1))."/"
                                                            )
                                                        )
                                                    @endphp
                                                    <span style="grid-area: count; padding-right: 4px;">
                                                        ({{ $filteredFiles->count() }})
                                                    </span>
                                                    <span
                                                        class="text-info"
                                                        style="grid-area: size; white-space: nowrap; text-align: right;"
                                                        title="{{ $filteredFiles->sum('size') }}&nbsp;B"
                                                    >
                                                        {{ App\Helpers\StringHelper::formatBytes($filteredFiles->sum('size'), 2) }}
                                                    </span>
                                                @endif
                                            </span>
                                        </summary>
                                @endfor
                                @break
                            @endif
                        @endforeach
                    @endforeach
                </div>
                <div class="data-table-wrapper" x-show="tab === 'list'">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('common.name') }}</th>
                                <th>{{ __('torrent.size') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($torrent->files as $index => $file)
                                <tr>
                                    <td style="text-align: right;">{{ $index + 1 }}</td>
                                    <td style="text-align: left;">{{ $file->name }}</td>
                                    <td style="text-align: right;" title="{{ $file->size }}&nbsp;B">{{ $file->getSize() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </dialog>
    </li>
    <li class="form__group form__group--short-horizontal">
        @livewire('bookmark-button', ['torrent' => $torrent, 'isBookmarked' => $torrent->bookmarks_exists, 'user' => auth()->user()])
    </li>
    @if ($playlists->count() > 0)
        <li x-data class="form__group form__group--short-horizontal">
            <button class="form__button form__button--outlined form__button--centered" x-on:click.stop="$refs.dialog.showModal()">
                <i class="{{ config('other.font-awesome') }} fa-list-ol"></i> {{ __('torrent.add-to-playlist') }}
            </button>
            <dialog class="dialog" x-ref="dialog">
                <h4 class="dialog__heading">
                    Add Torrent To Playlist
                </h4>
                <form
                    class="dialog__form"
                    method="POST"
                    action="{{ route('playlist_torrents.store') }}"
                    x-on:click.outside="$refs.dialog.close()"
                >
                    @csrf
                    <input type="hidden" name="torrent_id" value="{{ $torrent->id }}">
                    <p class="form__group">
                        <select id="playlist_id" name="playlist_id" class="form__select">
                            @foreach ($playlists as $playlist)
                                <option value="{{ $playlist->id }}">{{ $playlist->name }}</option>
                            @endforeach
                        </select>
                        <label for="playlist_id" class="form__label form__label--floating">
                            Your Playlists
                        </label>
                    </p>
                    <p class="form__group" style="text-align: left">
                        <button class="form__button form__button--filled">
                            {{ __('common.save') }}
                        </button>
                        <button formmethod="dialog" formnovalidate class="form__button form__button--outlined">
                            {{ __('common.cancel') }}
                        </button>
                    </p>
                </form>
            </dialog>
        </li>
    @endif
    @if (
        $torrent->seeders <= 2
        /* $history is used inside the resurrection code below and assumes is set if torrent->seeders are equal to 0 */
        && null !== ($history = $user->history->where('torrent_id', $torrent->id)->first())
        && $history->seeder == 0
        && $history->active == 1
    )
        <li class="form__group form__group--short-horizontal">
            <form action="{{ route('reseed', ['id' => $torrent->id]) }}" method="POST" style="display: inline;">
                @csrf
                <button class="form__button form__button--outlined form__button--centered">
                    <i class='{{ config("other.font-awesome") }} fa-envelope'></i> {{ __('torrent.request-reseed') }}
                </button>
            </form>
        </li>
    @endif
    @if (DB::table('resurrections')->where('torrent_id', '=', $torrent->id)->where('rewarded', '=', 0)->exists())
        <li class="form__group form__group--short-horizontal">
            <button class="form__button form__button--outlined form__button--centered" disabled>
                {{ strtolower(__('graveyard.pending')) }}
            </button>
        </li>
    @elseif ($torrent->seeders == 0 && $torrent->created_at->lt(\Illuminate\Support\Carbon::now()->subDays(30)))
        <li class="form__group form__group--short-horizontal" x-data>
            <button class="form__button form__button--outlined form__button--centered" x-on:click.stop="$refs.dialog.showModal()">
                <i class="{{ config('other.font-awesome') }} fa-list-ol"></i> {{ __('graveyard.resurrect') }}
            </button>
            <dialog class="dialog" x-ref="dialog">
                <h4 class="dialog__heading">
                    {{ __('graveyard.resurrect') }} {{ strtolower(__('torrent.torrent')) }} ?
                </h4>
                <form
                    class="dialog__form"
                    method="POST"
                    action="{{ route('users.resurrections.store', ['user' => auth()->user()]) }}"
                    x-on:click.outside="$refs.dialog.close()"
                >
                    @csrf
                    <input type="hidden" name="torrent_id" value="{{ $torrent->id }}">
                    <p class="form__group">
                        {{ __('graveyard.howto') }}
                    </p>
                    <p>{!! __('graveyard.howto-desc1', ['name' => $torrent->name]) !!}
                        <span class="text-red text-bold">
                            {{ $history === null ? '0' : App\Helpers\StringHelper::timeElapsed($history->seedtime) }}
                        </span>
                        {{ strtolower(__('graveyard.howto-hits')) }}
                        <span class="text-red text-bold">
                            {{ $history === null ? App\Helpers\StringHelper::timeElapsed(config('graveyard.time')) : App\Helpers\StringHelper::timeElapsed($history->seedtime + config('graveyard.time')) }}
                        </span>
                        {{ strtolower(__('graveyard.howto-desc2')) }}
                        <span class="badge-user text-bold text-pink" style="background-image:url({{ url('/img/sparkels.gif') }};">
                            {{ config('graveyard.reward') }} {{ __('torrent.freeleech') }} Token(s)!
                        </span>
                    </p>
                    <p class="form__group" style="text-align: left">
                        <button class="form__button form__button--filled">
                            {{ __('graveyard.resurrect') }}
                        </button>
                        <button formmethod="dialog" formnovalidate class="form__button form__button--outlined">
                            {{ __('common.cancel') }}
                        </button>
                    </p>
                </form>
            </dialog>
        </li>
    @endif
    <li x-data class="form__group form__group--short-horizontal">
        <button class="form__button form__button--outlined form__button--centered" x-on:click.stop="$refs.dialog.showModal()">
            <i class="{{ config('other.font-awesome') }} fa-fw fa-eye"></i> {{ __('common.report') }}
        </button>
        <dialog class="dialog" x-ref="dialog">
            <h4 class="dialog__heading">
                {{ __('common.report') }} {{ strtolower(__('torrent.torrent')) }}: {{ $torrent->name }}
            </h4>
            <form
                class="dialog__form"
                method="POST"
                action="{{ route('report_torrent', ['id' => $torrent->id]) }}"
                x-on:click.outside="$refs.dialog.close()"
            >
                @csrf
                <input type="hidden" name="torrent_id" value="{{ $torrent->id }}">
                <p class="form__group">
                    <textarea
                        id="message"
                        class="form__textarea"
                        name="message"
                        required
                    ></textarea>
                    <label for="report_reason" class="form__label form__label--floating" for="message">
                        {{ __('common.reason') }}
                    </label>
                </p>
                <p class="form__group" style="text-align: left">
                    <button class="form__button form__button--filled">
                        {{ __('common.report') }}
                    </button>
                    <button formmethod="dialog" formnovalidate class="form__button form__button--outlined">
                        {{ __('common.cancel') }}
                    </button>
                </p>
            </form>
        </dialog>
    </li>
</menu>

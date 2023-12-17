@extends('layout.default')

@section('title')
    <title>External Tracker - {{ $torrent?->name ?? 'Not Found' }} - {{ config('other.title') }}</title>
@endsection

@section('meta')
    <meta name="description" content="{{ __('torrent.peers') }}">
@endsection

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('torrents.index') }}" class="breadcrumb__link">
            {{ __('torrent.torrents') }}
        </a>
    </li>
    <li class="breadcrumbV2">
        <a href="{{ route('torrents.show', ['id' => $id]) }}" class="breadcrumb__link">
            {{ $torrent?->name ?? 'Not Found' }}
        </a>
    </li>
    <li class="breadcrumb--active">
        {{ __('torrent.peers') }}
    </li>
@endsection

@section('nav-tabs')
    <li class="nav-tabV2">
        <a class="nav-tab__link" href="{{ route('peers', ['id' => $id]) }}">
            {{ __('torrent.peers') }}
        </a>
    </li>
    <li class="nav-tabV2">
        <a class="nav-tab__link" href="{{ route('history', ['id' => $id]) }}">
            {{ __('torrent.history') }}
        </a>
    </li>
    <li class="nav-tab--active">
        <a class="nav-tab--active__link" href="{{ route('torrents.external_tracker', ['id' => $id]) }}">
            External Tracker
        </a>
    </li>
@endsection

@section('main')
    @if ($externalTorrent === true)
        <section class="panelV2">
            <h2 class="panel__heading">{{ __('torrent.torrent') }}</h2>
            <div class="panel__body">
                External tracker not enabled.
            </div>
        </section>
    @elseif ($externalTorrent === false)
        <section class="panelV2">
            <h2 class="panel__heading">{{ __('torrent.torrent') }}</h2>
            <div class="panel__body">
                Torrent not found.
            </div>
        </section>
    @elseif ($externalTorrent === [])
        <section class="panelV2">
            <h2 class="panel__heading">{{ __('torrent.torrent') }}</h2>
            <div class="panel__body">
                Tracker returned an error.
            </div>
        </section>
    @else
        <section class="panelV2">
            <h2 class="panel__heading">{{ __('torrent.torrent') }} {{ __('torrent.peers') }}</h2>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('common.user') }}</th>
                            <th>{{ __('torrent.progress') }}</th>
                            <th>{{ __('common.upload') }}</th>
                            <th>{{ __('common.download') }}</th>
                            <th>{{ __('common.ip') }}</th>
                            <th>{{ __('common.port') }}</th>
                            <th>{{ __('torrent.started') }}</th>
                            <th>{{ __('torrent.last-update') }}</th>
                            <th>{{ __('common.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($externalTorrent['peers'] ?? [] as $peer)
                            <tr>
                                <td>
                                    @if (null !== ($user = \App\Models\User::find($peer['user_id'])))
                                        @if ($torrent === null)
                                            <x-user_tag :user="$user" :anon="true" />
                                        @else
                                            <x-user_tag :user="$user" :anon="
                                                $user->hidden == 1
                                                || $user->peer_hidden == 1
                                                || $user->privacy?->show_peer === 0
                                                || ($user->id == $torrent->user->id && $torrent->anon == 1)
                                            " />
                                        @endif
                                    @else
                                        User not found
                                    @endif
                                </td>
                                <td>
                                    @if ($torrent === null)
                                        Torrent size not available
                                    @else
                                        @php
                                            $progress = 100 * ($peer['downloaded'] % ($torrent->size)) / $torrent->size
                                        @endphp
                                        @if (0 < $progress && $progress < 1)
                                            1%
                                        @elseif (99 < $progress && $progress < 100)
                                            99%
                                        @else
                                            {{ round($progress) }}
                                        @endif
                                    @endif
                                </td>
                                <td class="text-green">{{ \App\Helpers\StringHelper::formatBytes($peer['uploaded'] ?? 0, 2) }}</td>
                                <td class="text-red">{{ \App\Helpers\StringHelper::formatBytes($peer['downloaded'] ?? 0, 2) }}</td>
                                <td>{{ \App\Helpers\StringHelper::formatBytes($peer['left'] ?? 0, 2) }}</td>
                                @if (auth()->user()->group->is_modo || auth()->id() == $peer->user_id)
                                    <td>{{ $peer['ip_address'] }}</td>
                                    <td>{{ $peer['port'] }}</td>
                                @else
                                    <td>---</td>
                                    <td>---</td>
                                @endif
                                <td>
                                    @php
                                        $updatedAt = \Illuminate\Support\Carbon::createFromTimestampUTC($peer['updated_at'])
                                    @endphp
                                    <time datetime="{{ $updatedAt }}" title="{{ $updatedAt }}">
                                        {{ $updatedAt ? $updatedAt->diffForHumans() : 'N/A' }}
                                    </time>
                                </td>
                                <td class="{{ $peer['is_active'] ? ($peer['is_seeder'] ? 'text-green' : 'text-red') : 'text-orange' }}">
                                    @if ($peer['is_active'])
                                        @if ($peer['is_seeder'])
                                            {{ __('torrent.seeder') }}
                                        @else
                                            {{ __('torrent.leecher') }}
                                        @endif
                                    @else
                                        Inactive
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @section('sidebar')
            <section class="panelV2">
                <h2 class="panel__heading">{{ __('torrent.torrent') }}</h2>
                <dl class="key-value">
                    <dt>{{ __('common.moderation') }}</dt>
                    <dd>{{ $externalTorrent['status'] }}</dd>
                    <dt>{{ __('torrent.seeders') }}</dt>
                    <dd>{{ $externalTorrent['seeders'] }}</dd>
                    <dt>{{ __('torrent.leechers') }}</dt>
                    <dd>{{ $externalTorrent['leechers'] }}</dd>
                    <dt>{{ __('torrent.completed-times') }}</dt>
                    <dd>{{ $externalTorrent['times_completed'] }}</dd>
                    <dt>Download Factor</dt>
                    <dd>{{ $externalTorrent['download_factor'] }}</dd>
                    <dt>Upload Factor</dt>
                    <dd>{{ $externalTorrent['upload_factor'] }}</dd>
                    <dt>Deleted</dt>
                    <dd>{{ $externalTorrent['is_deleted'] ? __('common.yes') : __('common.no') }}</dd>
                </dl>
            </section>
        @endsection
    @endif
@endsection

<div style="display: flex; flex-direction: column; gap: 1rem;">
    <section class="panelV2 user-uploads__filters">
        <h2 class="panel__heading">{{ __('common.search') }}</h2>
        <div class="panel__body">
            <div class="form__group--horizontal">
                <p class="form__group">
                    <input id="name" wire:model="name" class="form__text" placeholder=" " autofocus="">
                    <label class="form__label form__label--floating" for="name">{{ __('torrent.name') }}</label>
                </p>
            </div>
            <div class="form__group--short-horizontal">
                <div class="form__group">
                    <fieldset class="form__fieldset">
                        <legend class="form__legend">{{ __('torrent.filters') }}</legend>
                        <div class="form__fieldset-checkbox-container">
                            <p class="form__group">
                                <label style="user-select: none" class="form__label" x-data="{ state: @entangle('personalRelease'), ...ternaryCheckbox() }">
                                    <input
                                        type="checkbox"
                                        class="user-uploads__checkbox"
                                        x-init="updateTernaryCheckboxProperties($el, state)"
                                        x-on:click="state = getNextTernaryCheckboxState(state); updateTernaryCheckboxProperties($el, state)"
                                        x-bind:checked="state === 'include'"
                                    >
                                    {{ __('torrent.downloaded') }}
                                </label>
                            </p>
                        </div>
                    </fieldset>
                </div>
                <div class="form__group">
                    <fieldset class="form__fieldset">
                        <legend class="form__legend">{{ __('torrent.moderation') }}</legend>
                        <div class="form__fieldset-checkbox-container">
                            <p class="form__group">
                                <label class="form__label">
                                    <input class="user-uploads__checkbox" type="checkbox" value="{{ \App\Models\Torrent::PENDING }}" wire:model="status">
                                    {{ __('torrent.pending') }}
                                </label>
                            </p>
                            <p class="form__group">
                                <label class="form__label">
                                    <input class="user-uploads__checkbox" type="checkbox" value="{{ \App\Models\Torrent::APPROVED }}" wire:model="status">
                                    {{ __('torrent.approved') }}
                                </label>
                            </p>
                            <p class="form__group">
                                <label class="form__label">
                                    <input class="user-uploads__checkbox" type="checkbox" value="{{ \App\Models\Torrent::REJECTED }}" wire:model="status">
                                    {{ __('torrent.rejected') }}
                                </label>
                            </p>
                            <p class="form__group">
                                <label class="form__label">
                                    <input class="user-uploads__checkbox" type="checkbox" value="{{ \App\Models\Torrent::POSTPONED }}" wire:model="status">
                                    Postponed
                                </label>
                            </p>
                        </div>
                    </fieldset>
                </div>
                <div class="form__group">
                    <fieldset class="form__fieldset">
                        <legend class="form__legend">Precision</legend>
                        <div class="form__fieldset-checkbox-container">
                            <p class="form__group">
                                <label class="form__label">
                                    <input type="checkbox" class="user-uploads__checkbox" wire:model="showMorePrecision">
                                    Show more precision
                                </label>
                            </p>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </section>
    <section class="panelV2">
        <h2 class="panel__heading">{{ __('user.uploads') }}</h2>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                <th class="user-uploads__name-header" wire:click="sortBy('name')" role="columnheader button">
                    {{ __('torrent.name') }}
                    @include('livewire.includes._sort-icon', ['field' => 'name'])
                </th>
                <th class="user-uploads__size-header" wire:click="sortBy('size')" role="columnheader button">
                    {{ __('torrent.size') }}
                    @include('livewire.includes._sort-icon', ['field' => 'size'])
                </th>
                <th class="user-uploads__seeders-header" wire:click="sortBy('seeders')" role="columnheader button" title="{{ __('torrent.seeders') }}">
                    <i class="fas fa-arrow-alt-circle-up"></i>
                    @include('livewire.includes._sort-icon', ['field' => 'seeders'])
                </th>
                <th class="user-uploads__leechers-header" wire:click="sortBy('leechers')" role="columnheader button" title="{{ __('torrent.leechers') }}">
                    <i class="fas fa-arrow-alt-circle-down"></i>
                    @include('livewire.includes._sort-icon', ['field' => 'leechers'])
                </th>
                <th class="user-uploads__times-header" wire:click="sortBy('times_completed')" role="columnheader button" title="{{ __('torrent.completed') }}">
                    <i class="fas fa-check-circle"></i>
                    @include('livewire.includes._sort-icon', ['field' => 'times_completed'])
                </th>
                <th class="user-uploads__tips-header" wire:click="sortBy('tips_sum_cost')" role="columnheader button" title="{{ __('bon.tips') }}">
                    <i class="fas fa-coins"></i>
                    @include('livewire.includes._sort-icon', ['field' => 'tips_sum_cost'])
                </th>
                <th class="user-uploads__thanks-header" wire:click="sortBy('thanks_count')" role="columnheader button" title="{{ __('torrent.thanks') }}">
                    <i class="fas fa-heart"></i>
                    @include('livewire.includes._sort-icon', ['field' => 'thanks_count'])
                </th>
                <th class="user-uploads__created-at-header" wire:click="sortBy('created_at')" role="columnheader button">
                    {{ __('torrent.uploaded') }}
                    @include('livewire.includes._sort-icon', ['field' => 'created_at'])
                </th>
                <th class="user-uploads__personal-release-header" wire:click="sortBy('personal_release')" role="columnheader button" title="{{ __('torrent.personal-release') }}">
                    <i class="fas fa-user-plus"></i>
                    @include('livewire.includes._sort-icon', ['field' => 'status'])
                </th>
                <th class="user-uploads__status-header" wire:click="sortBy('status')" role="columnheader button" title="{{ __('torrent.approved') }}">
                    <i class="fas fa-tasks"></i>
                    @include('livewire.includes._sort-icon', ['field' => 'status'])
                </th>
                </thead>
                <tbody>
                @foreach ($uploads as $torrent)
                    <tr>
                        <td>
                            @if ($torrent->internal)
                                <i class="{{ config('other.font-awesome') }} fa-magic" style="color: #baaf92;"></i>
                            @endif
                            <a class="user-uploads__name" href="{{ route('torrents.show', ['id' => $torrent->id]) }}">
                                {{ $torrent->name }}
                            </a>
                        </td>
                        <td class="user-uploads__size">
                            {{ App\Helpers\StringHelper::formatBytes($torrent->size) }}
                        </td>
                        <td class="user-uploads__seeders">
                            <a href="{{ route('peers', ['id' => $torrent->id]) }}">
                                <span class='text-green'>
                                    {{ $torrent->seeders }}
                                </span>
                            </a>
                        </td>
                        <td class="user-uploads__leechers">
                            <a href="{{ route('peers', ['id' => $torrent->id]) }}">
                                <span class='text-red'>
                                    {{ $torrent->leechers }}
                                </span>
                            </a>
                        </td>
                        <td class="user-uploads__times">
                            <a href="{{ route('history', ['id' => $torrent->id]) }}">
                                <span class='text-orange'>
                                    {{ $torrent->times_completed }}
                                </span>
                            </a>
                        </td>
                        <td class="user-uploads__tips">
                            {{ $torrent->tips_sum_cost ?? 0 }}
                        </td>
                        <td class="user-uploads__thanks">
                            {{ $torrent->thanks_count ?? 0 }}
                        </td>
                        <td class="user-uploads__created-at">
                            <time datetime="{{ $torrent->created_at }}" title="{{ $torrent->created_at }}">
                                @if ($showMorePrecision)
                                    {{ $torrent->created_at ?? 'N/A' }}
                                @else
                                    {{ $torrent->created_at === null ? 'N/A' : \explode(" ", $torrent->created_at)[0] }}
                                @endif
                            </time>
                        </td>
                        <td class="user-uploads__personal-release">
                            @if ($torrent->personal_release === 1)
                                <i class="{{ config('other.font-awesome') }} fa-check text-green" title="{{ __('torrent.personal-release') }}"></i>
                            @else
                                <i class="{{ config('other.font-awesome') }} fa-times text-red" title="{{ __('torrent.not-personal-release') }}"></i>
                            @endif
                        </td>
                        <td class="user-uploads__status">
                            @switch($torrent->status)
                                @case(\App\Models\Torrent::PENDING)
                                    <span title="{{ __('torrent.pending') }}" class="{{ config('other.font-awesome') }} fa-tasks text-orange"></span>
                                    @break
                                @case(\App\Models\Torrent::APPROVED)
                                    <span title="{{ __('torrent.approved') }}" class="{{ config('other.font-awesome') }} fa-check text-green"></span>
                                    @break
                                @case(\App\Models\Torrent::REJECTED)
                                    <span title="{{ __('torrent.rejected') }}" class ="{{ config('other.font-awesome') }} fa-times text-red"></span>
                                    @break
                                @case(\App\Models\Torrent::POSTPONED)
                                    <span title="Postponed" class ="{{ config('other.font-awesome') }} fa-hourglass text-red"></span>
                                    @break
                            @endswitch
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $uploads->links('partials.pagination') }}
    </section>
    <script nonce="{{ HDVinnie\SecureHeaders\SecureHeaders::nonce('script') }}">
        function ternaryCheckbox() {
            return {
                updateTernaryCheckboxProperties(el, state) {
                    el.indeterminate = (state === 'exclude');
                    el.checked = (state === 'include');
                },
                getNextTernaryCheckboxState(state) {
                    return (state === 'include') ? 'exclude' : (state === 'exclude' ? 'any' : 'include');
                }
            }
        }
    </script>
</div>
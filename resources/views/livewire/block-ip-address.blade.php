<section class="panelV2">
    <header class="panel__header">
        <h2 class="panel__heading">{{ __('staff.blocked-ips') }}</h2>
        <div class="panel__actions">
            <div class="panel__action" x-data>
                <button
                    class="form__button form__button--outlined"
                    x-on:click.stop="$refs.dialog.showModal()"
                >
                    {{ __('common.add') }}
                </button>
                <dialog class="dialog" x-ref="dialog">
                    <h3 class="dialog__heading">
                        Block Ip Address
                    </h3>
                    <form
                        class="dialog__form"
                        x-on:click.outside="$refs.dialog.close()"
                        x-on:submit.prevent="$refs.dialog.close()"
                    >
                        <p class="form__group">
                            <input
                                id="ipAddress"
                                class="form__text"
                                name="ipAddress"
                                placeholder=" "
                                wire:model.defer="ipAddress"
                            >
                            <label class="form__label form__label--floating" for="ipAddress">
                                Ip Address
                            </label>
                        </p>
                        <p class="form__group">
                            <textarea
                                id="reason"
                                class="form__textarea"
                                name="reason"
                                placeholder=" "
                                wire:model.defer="reason"
                            ></textarea>
                            <label class="form__label form__label--floating" for="reason">
                                Reason
                            </label>
                        </p>
                        <p class="form__group">
                            <button
                                class="form__button form__button--filled"
                                wire:click="store"
                                x-on:click="$refs.dialog.close()"
                            >
                                {{ __('common.save') }}
                            </button>
                            <button formmethod="dialog" formnovalidate class="form__button form__button--outlined">
                                {{ __('common.cancel') }}
                            </button>
                        </p>
                    </form>
                </dialog>
            </div>
            <div class="panel__action">
                <div class="form__group">
                    <select
                        id="quantity"
                        class="form__select"
                        wire:model="perPage"
                        required
                    >
                        <option>25</option>
                        <option>50</option>
                        <option>100</option>
                    </select>
                    <label class="form__label form__label--floating" for="quantity">
                        {{ __('common.quantity') }}
                    </label>
                </div>
            </div>
        </div>
    </header>
    <div class="data-table-wrapper">
        <table class="data-table">
            <tbody>
            <tr>
                <th>{{ __('common.no') }}</th>
                <th>{{ __('common.user') }}</th>
                <th>{{ __('common.ip') }}</th>
                <th>{{ __('common.reason') }}</th>
                <th>{{ __('common.created_at') }}</th>
                <th>{{ __('user.expires-on') }}</th>
            </tr>
            @forelse ($ipAddresses as $ipAddress)
                <tr>
                    <td>{{ $ipAddress->id }}</td>
                    <td>
                        <x-user_tag :anon="false" :user="$ipAddress->user" />
                    </td>
                    <td>{{ $ipAddress->ip_address }}</td>
                    <td>{{ $ipAddress->reason }}</td>
                    <td>
                        <time datetime="{{ $ipAddress->created_at }}" title="{{ $ipAddress->created_at }}">
                            {{ $ipAddress->created_at }}
                        </time>
                    </td>
                    <td>
                        <time datetime="{{ $ipAddress->expires_at ?? 'Never' }}" title="{{ $ipAddress->expires_at }}">
                            {{ $ipAddress->expires_at ?? 'Never'}}
                        </time>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No blocked ip addresses</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $ipAddresses->links('partials.pagination') }}
</section>


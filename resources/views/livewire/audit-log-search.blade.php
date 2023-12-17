<section class="panelV2">
    <header class="panel__header">
        <h2 class="panel__heading">{{ __('staff.audit-log') }}</h2>
        <div class="panel__actions">
            <div class="panel__action">
                <div class="form__group">
                    <input
                        id="username"
                        class="form__text"
                        type="text"
                        wire:model="username"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="username">
                        Username
                    </label>
                </div>
            </div>
            <div class="panel__action">
                <div class="form__group">
                    <select
                        id="model"
                        x-data="{ selected: '' }"
                        x-model="selected"
                        x-bind:class="selected === '' ? 'form__select--default' : ''"
                        class="form__select"
                        wire:model="modelName"
                        required
                    >
                        <option selected value="">All</option>
                        @foreach ($modelNames as $modelName)
                            <option value="{{ $modelName }}">{{ $modelName }}</option>
                        @endforeach
                    </select>
                    <label class="form__label form__label--floating" for="model">
                        Model Name
                    </label>
                </div>
            </div>
            <div class="panel__action">
                <div class="form__group">
                    <input
                        id="modelId"
                        class="form__text"
                        type="text"
                        wire:model="modelId"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="modelId">
                        Model Id
                    </label>
                </div>
            </div>
            <div class="panel__action">
                <div class="form__group">
                    <select
                        id="action"
                        class="form__select"
                        wire:model="action"
                        required
                        x-data="{ selected: '' }"
                        x-model="selected"
                        x-bind:class="selected === '' ? 'form__select--default' : ''"
                    >
                        <option selected value="">All</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                    </select>
                    <label class="form__label form__label--floating" for="action">
                        Action
                    </label>
                </div>
            </div>
            <div class="panel__action">
                <div class="form__group">
                    <input
                        id="record"
                        class="form__text"
                        type="text"
                        wire:model="record"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="record">
                        Record
                    </label>
                </div>
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
            <thead>
            <tr>
                <th>#</th>
                <th>{{ __('common.action') }}</th>
                <th>Model</th>
                <th>Model ID</th>
                <th>By</th>
                <th>Changes</th>
                <th>{{ __('user.created-on') }}</th>
                <th>{{ __('common.action') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($audits as $audit)
                <tr>
                    <td>{{ $audit->id }}</td>
                    <td><span class="@if($audit->action === 'create') text-green @elseif($audit->action === 'update') text-yellow @elseif($audit->action === 'delete') text-red @endif">{{ strtoupper($audit->action) }}</span></td>
                    <td>{{ $audit->model_name }}</td>
                    <td>{{ $audit->model_entry_id }}</td>
                    <td>
                        <a href="{{ route('users.show', ['user' => $audit->user]) }}">
                            {{ $audit->user->username }}
                        </a>
                    </td>
                    <td>
                        <ul>
                            @foreach ($audit->values as $key => $value)
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
                    <td>
                        <time
                                datetime="{{ $audit->created_at }}"
                                title="{{ $audit->created_at }}"
                        >
                            {{ $audit->created_at->diffForHumans() }}
                        </time>
                    </td>
                    <td>
                        <menu class="data-table__actions">
                            <li class="data-table__action">
                                <form
                                        method="POST"
                                        action="{{ route('staff.audits.destroy', ['audit' => $audit]) }}"
                                        x-data
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                            x-on:click.prevent="Swal.fire({
                                                    title: 'Are you sure?',
                                                    text: 'Are you sure you want to delete this audit log entry?',
                                                    icon: 'warning',
                                                    showConfirmButton: true,
                                                    showCancelButton: true,
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $root.submit();
                                                    }
                                                })"
                                            class="form__button form__button--text"
                                    >
                                        {{ __('common.delete') }}
                                    </button>
                                </form>
                            </li>
                        </menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        No audits
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $audits->links('partials.pagination') }}
</section>


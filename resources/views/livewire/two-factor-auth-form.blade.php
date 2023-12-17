<section class="panelV2">
    <header class="panel__header">
        <h2 class="panel__heading">{{ __('Two Factor Authentication') }}</h2>
    </header>
    <div class="panel__body">
        @if ($this->enabled)
            @if ($showingConfirmation)
                <span class="text-warning">{{ __('Finish enabling two factor authentication.') }}</span>
            @else
                <span class="text-success">{{ __('You have enabled two factor authentication.') }}</span>
            @endif
        @else
            <span class="text-danger">{{ __('You have not enabled two factor authentication.') }}</span>
        @endif

        <div>
            <span class="text-muted">
                {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
            </span>
        </div>

        @if ($this->enabled)
            @if ($showingQrCode)
                <div>
                    <p class="text-info">
                        @if ($showingConfirmation)
                            {{ __('To finish enabling two factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.') }}
                        @else
                            {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                        @endif
                    </p>
                </div>

                <div class="twoStep__qrCode">
                    {!! $this->user->twoFactorQrCodeSvg() !!}
                </div>

                <div>
                    <p>
                        {{ __('Setup Key') }}: {{ decrypt($this->user->two_factor_secret) }}
                    </p>
                </div>

                @if ($showingConfirmation)
                    <div>
                        <label for="code" value="{{ __('Code') }}"></label>

                        <input id="code"
                               name="code"
                               class="form__text"
                               type="text"
                               inputmode="numeric"
                               autofocus
                               autocomplete="one-time-code"
                               wire:model="code"
                               wire:keydown.enter="confirmTwoFactorAuthentication" />

                        @error('code') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif
            @endif

            @if ($showingRecoveryCodes)
                <div class="panel__body">
                    <span class="text-danger">
                        {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                    </span>

                    <pre>
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div>{{ $code }}</div>
                      @endforeach
                    </pre>
                </div>
            @endif
        @endif

        <div>
            @if (! $this->enabled)
                <button class="form__button form__button--filled" wire:click="enableTwoFactorAuthentication" wire:loading.attr="disabled">
                    {{ __('Enable') }}
                </button>
            @else
                @if ($showingRecoveryCodes)
                    <button class="form__button form__button--filled" wire:click="regenerateRecoveryCodes">
                        {{ __('Regenerate Recovery Codes') }}
                    </button>
                @elseif ($showingConfirmation)
                    <button class="form__button form__button--filled" type="button" wire:click="confirmTwoFactorAuthentication" wire:loading.attr="disabled">
                        {{ __('Confirm') }}
                    </button>
                @else
                    <button class="form__button form__button--filled" wire:click="showRecoveryCodes">
                        {{ __('Show Recovery Codes') }}
                    </button>
                @endif

                @if ($showingConfirmation)
                    <button class="form__button form__button--filled" wire:click="disableTwoFactorAuthentication" wire:loading.attr="disabled">
                        {{ __('Cancel') }}
                    </button>
                @else
                    <button class="form__button form__button--filled" wire:click="disableTwoFactorAuthentication" wire:loading.attr="disabled">
                        {{ __('Disable') }}
                    </button>
                @endif
            @endif
        </div>

    </div>
</section>
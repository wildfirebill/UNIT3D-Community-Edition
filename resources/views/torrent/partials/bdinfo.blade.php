<section class="panelV2 torrent-bdinfo" x-data>
    <header class="panel__header">
        <h2 class="panel__heading">
            <i class="{{ config("other.font-awesome") }} fa-compact-disc"></i>
            BDInfo
        </h2>
        <div class="panel__actions">
            <div class="panel__action">
                <button
                    class="form__button form__button--text"
                    x-data
                    x-on:click.stop="
                        navigator.clipboard.writeText($refs.bdinfo.textContent);
                        Swal.fire({
                              toast: true,
                              position: 'top-end',
                              showConfirmButton: false,
                              timer: 3000,
                              icon: 'success',
                              title: 'Copied to clipboard!'
                        })
                    "
                >
                    Copy
                </button>
            </div>
        </div>
    </header>
    <div class="panel__body">
        <div class="bbcode-rendered">
            <pre><code x-ref="bdinfo">{{ $torrent->bdinfo }}</code></pre>
        </div>
    </div>
</section>

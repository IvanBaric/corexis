@props([
    'enabled' => true,
    'storageKey' => 'corexis_cookie_consent_v1',
    'title' => __('corexis::corexis.cookie_consent.title'),
    'message' => __('corexis::corexis.cookie_consent.message'),
    'acceptLabel' => __('corexis::corexis.cookie_consent.accept'),
    'policyUrl' => null,
    'policyLabel' => __('corexis::corexis.cookie_consent.policy'),
    'ariaLabel' => __('corexis::corexis.cookie_consent.aria_label'),
])

@if ($enabled)
    <div
        {{ $attributes->class([
            'fixed inset-x-0 bottom-0 z-[120] px-4 pb-4 transition duration-200 sm:px-6',
        ]) }}
        data-corexis-cookie-consent
        data-storage-key="{{ $storageKey }}"
        role="region"
        aria-label="{{ $ariaLabel }}"
        hidden
    >
        <div
            class="mx-auto flex max-w-5xl flex-col gap-4 rounded-xl border border-zinc-200 bg-white/95 p-4 text-zinc-900 shadow-lg shadow-zinc-950/10 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-950/95 dark:text-white sm:flex-row sm:items-center sm:justify-between sm:gap-6"
            style="--corexis-cookie-primary: var(--niva-primary-700, #9F2E61); --corexis-cookie-primary-hover: var(--niva-primary-800, #7F214C);"
        >
            <div class="min-w-0">
                <p class="text-sm font-semibold leading-5">{{ $title }}</p>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $message }}</p>
            </div>

            <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                @if (filled($policyUrl))
                    <a
                        href="{{ $policyUrl }}"
                        class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-lg px-3 text-sm font-semibold text-zinc-700 transition hover:text-[var(--corexis-cookie-primary-hover)] dark:text-zinc-200 dark:hover:text-white"
                    >
                        {{ $policyLabel }}
                    </a>
                @endif

                <button
                    type="button"
                    data-corexis-cookie-consent-accept
                    class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-lg bg-[var(--corexis-cookie-primary)] px-4 text-sm font-semibold text-white shadow-sm shadow-zinc-950/10 transition hover:bg-[var(--corexis-cookie-primary-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--corexis-cookie-primary)] focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-950"
                >
                    {{ $acceptLabel }}
                </button>
            </div>
        </div>
    </div>

    @once
        <script>
            (() => {
                const acceptedValue = 'accepted';

                const readConsent = (key) => {
                    try {
                        return window.localStorage.getItem(key);
                    } catch (error) {
                        return null;
                    }
                };

                const writeConsent = (key) => {
                    try {
                        window.localStorage.setItem(key, acceptedValue);
                    } catch (error) {
                        return;
                    }
                };

                const hideBanner = (banner) => {
                    banner.classList.add('translate-y-2', 'opacity-0');

                    window.setTimeout(() => {
                        banner.hidden = true;
                    }, 180);
                };

                const showBanner = (banner) => {
                    banner.classList.add('translate-y-2', 'opacity-0');
                    banner.hidden = false;

                    window.requestAnimationFrame(() => {
                        banner.classList.remove('translate-y-2', 'opacity-0');
                    });
                };

                const initializeCookieConsent = () => {
                    document.querySelectorAll('[data-corexis-cookie-consent]').forEach((banner) => {
                        if (banner.dataset.initialized === 'true') {
                            return;
                        }

                        banner.dataset.initialized = 'true';

                        const storageKey = banner.dataset.storageKey || 'corexis_cookie_consent_v1';

                        if (readConsent(storageKey) === acceptedValue) {
                            banner.hidden = true;

                            return;
                        }

                        banner.querySelectorAll('[data-corexis-cookie-consent-accept]').forEach((button) => {
                            button.addEventListener('click', () => {
                                writeConsent(storageKey);
                                hideBanner(banner);
                            });
                        });

                        showBanner(banner);
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initializeCookieConsent, { once: true });
                } else {
                    initializeCookieConsent();
                }

                document.addEventListener('livewire:navigated', initializeCookieConsent);
            })();
        </script>
    @endonce
@endif

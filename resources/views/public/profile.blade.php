<x-layouts::front.main :title="__('profile.title')">
    @push('meta')
        <meta name="description" content="{{ __('profile.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" data-page="profile-page">
        <header data-profile-header-card
            class="mb-8 rounded-2xl border border-brand-600/25 bg-linear-to-r from-[#edd2c7]/35 via-white to-[#f1bd4d]/15 p-4 sm:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#793d3d]">
                        {{ __('profile.kicker') }}</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">
                        {{ __('profile.heading') }}</h1>
                    <p class="mt-2 max-w-3xl wrap-break-word text-sm leading-relaxed text-gray-600">
                        {{ __('profile.summary') }}</p>
                </div>
                <div class="w-full md:w-auto md:max-w-xs">
                    <p class="mb-2 max-w-full wrap-break-word text-xs leading-relaxed text-gray-600"
                        data-profile-contact-helper-text>
                        {{ __('profile.contact_modal.description') }}
                    </p>
                    @livewire('profile-contact-modal')
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                data-profile-status>
                {{ session('status') }}
            </div>
        @endif

        @if ($requiresTermsAcceptance)
            <div x-data>
                <template x-teleport="body">
                    <div class="fixed inset-0 z-110" data-profile-terms-modal>
                        <div class="absolute inset-0 bg-black/50" aria-hidden="true"></div>
                        <section
                            class="relative m-0 grid h-full w-full place-items-center overflow-y-auto p-4">
                            <div class="my-auto w-full max-w-3xl rounded-2xl border border-amber-300 bg-amber-50 p-6 shadow-2xl"
                                data-profile-terms-modal-card>
                                <h2 class="text-base font-semibold text-amber-900">
                                    {{ __('profile.terms.title') }}
                                </h2>
                                <div
                                    class="prose prose-sm mt-3 max-h-72 overflow-y-auto max-w-none text-amber-900">
                                    {!! $termsHtml !!}
                                </div>

                                <form method="POST"
                                    action="{{ route(\App\SupportedLocales::routeName('profile.terms.accept')) }}"
                                    class="mt-5">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#793d3d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#5d2e2e]">
                                        {{ __('profile.terms.accept_button') }}
                                    </button>
                                </form>
                            </div>
                        </section>
                    </div>
                </template>
            </div>
        @endif

        <div
            class="{{ $requiresTermsAcceptance ? 'pointer-events-none select-none blur-sm' : '' }}">
            <nav class="mb-6" aria-label="{{ __('profile.tabs_aria') }}">
                <ul class="flex flex-wrap items-stretch gap-2 rounded-xl border border-gray-200 bg-white p-2"
                    data-profile-tabs-list>
                    @php($tabs = $requiresTermsAcceptance ? ['owner'] : ['overview', 'votings', 'sessions', 'received', 'messages', 'owner'])
                    @foreach ($tabs as $tab)
                        <li class="flex grow sm:grow-0">
                            <a href="{{ route(\App\SupportedLocales::routeName('profile'), ['tab' => $tab]) }}"
                                class="inline-flex min-h-10 w-full items-center justify-center rounded-lg px-3 py-2 text-xs font-medium transition sm:text-sm {{ $activeTab === $tab ? 'bg-[#793d3d] text-white' : 'text-gray-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                                data-profile-tab="{{ $tab }}">
                                {{ __('profile.tabs.' . $tab) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            @if ($activeTab === 'overview')
                <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6"
                    data-profile-panel="overview">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ __('profile.overview.title') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">{{ __('profile.overview.description') }}
                    </p>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('profile.overview.votings_count') }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">
                                {{ $userBallots->count() }}</p>
                        </article>
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('profile.overview.sessions_count') }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">
                                {{ count($loginSessions) }}</p>
                        </article>
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('profile.overview.pending_properties') }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">
                                {{ $pendingAssignments->count() }}</p>
                        </article>
                    </div>

                    <a href="{{ route(\App\SupportedLocales::routeName('password.request')) }}"
                        class="mt-6 inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:border-brand-600 hover:text-[#793d3d]">
                        {{ __('profile.overview.change_password') }}
                    </a>
                </section>
            @endif

            @if ($activeTab === 'votings')
                <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6"
                    data-profile-panel="votings">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ __('profile.votings.title') }}
                    </h2>

                    <div class="mt-4 space-y-6">
                        <div data-profile-votings-participated>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">
                                {{ __('profile.votings.participated_title') }}
                            </h3>

                            @if ($userBallots->isEmpty())
                                <div
                                    class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-6 py-8 text-center">
                                    <p class="text-sm text-gray-500">
                                        {{ __('profile.votings.empty') }}</p>
                                </div>
                            @else
                                <div class="mt-3 space-y-3 md:hidden"
                                    data-profile-votings-participated-cards>
                                    @foreach ($userBallots as $ballot)
                                        <article
                                            class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs"
                                            data-profile-votings-participated-row>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.votings.voting') }}</p>
                                            <p
                                                class="mt-1 text-sm font-medium text-gray-900 wrap-break-word">
                                                {{ $ballot['voting_name'] }}</p>
                                            <p
                                                class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.votings.voted_at') }}</p>
                                            <p class="mt-1 text-sm text-gray-700">
                                                {{ \App\Support\LocalizedDateFormatter::dateTime($ballot['voted_at']) }}
                                            </p>
                                        </article>
                                    @endforeach
                                </div>

                                <div
                                    class="mt-3 hidden overflow-x-auto rounded-xl border border-gray-200 md:block">
                                    <table class="min-w-lg divide-y divide-gray-200 sm:min-w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    {{ __('profile.votings.voting') }}</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    {{ __('profile.votings.voted_at') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @foreach ($userBallots as $ballot)
                                                <tr data-profile-votings-participated-row>
                                                    <td class="px-4 py-3 text-sm text-gray-800">
                                                        {{ $ballot['voting_name'] }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">
                                                        {{ \App\Support\LocalizedDateFormatter::dateTime($ballot['voted_at']) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div data-profile-votings-pending-active>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">
                                {{ __('profile.votings.pending_active_title') }}
                            </h3>

                            @if ($pendingActiveVotings->isEmpty())
                                <div
                                    class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-6 py-8 text-center">
                                    <p class="text-sm text-gray-500">
                                        {{ __('profile.votings.pending_active_empty') }}</p>
                                </div>
                            @else
                                <div class="mt-3">
                                    <a href="{{ route(\App\SupportedLocales::routeName('votings')) }}"
                                        data-profile-votings-pending-link
                                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#793d3d]">
                                        {{ __('profile.votings.go_to_front') }}
                                    </a>
                                </div>

                                <div class="mt-3 space-y-3 md:hidden"
                                    data-profile-votings-pending-cards>
                                    @foreach ($pendingActiveVotings as $voting)
                                        <article
                                            class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs"
                                            data-profile-votings-pending-row>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.votings.voting') }}</p>
                                            <p
                                                class="mt-1 text-sm font-medium text-gray-900 wrap-break-word">
                                                {{ $voting['voting_name'] }}</p>
                                            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <div>
                                                    <p
                                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                        {{ __('profile.votings.starts_at') }}</p>
                                                    <p class="mt-1 text-sm text-gray-700">
                                                        {{ \App\Support\LocalizedDateFormatter::date($voting['starts_at']) }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                        {{ __('profile.votings.ends_at') }}</p>
                                                    <p class="mt-1 text-sm text-gray-700">
                                                        {{ \App\Support\LocalizedDateFormatter::date($voting['ends_at']) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>

                                <div
                                    class="mt-3 hidden overflow-x-auto rounded-xl border border-gray-200 md:block">
                                    <table class="min-w-160 divide-y divide-gray-200 sm:min-w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    {{ __('profile.votings.voting') }}</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    {{ __('profile.votings.starts_at') }}</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    {{ __('profile.votings.ends_at') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @foreach ($pendingActiveVotings as $voting)
                                                <tr data-profile-votings-pending-row>
                                                    <td class="px-4 py-3 text-sm text-gray-800">
                                                        {{ $voting['voting_name'] }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">
                                                        {{ \App\Support\LocalizedDateFormatter::date($voting['starts_at']) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">
                                                        {{ \App\Support\LocalizedDateFormatter::date($voting['ends_at']) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div data-profile-votings-missed-closed>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">
                                {{ __('profile.votings.missed_closed_title') }}
                            </h3>

                            @if ($missedClosedVotings->isEmpty())
                                <div
                                    class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-6 py-8 text-center">
                                    <p class="text-sm text-gray-500">
                                        {{ __('profile.votings.missed_closed_empty') }}</p>
                                </div>
                            @else
                                <div class="mt-3 space-y-3 md:hidden"
                                    data-profile-votings-missed-cards>
                                    @foreach ($missedClosedVotings as $voting)
                                        <article
                                            class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs"
                                            data-profile-votings-missed-row>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.votings.voting') }}</p>
                                            <p
                                                class="mt-1 text-sm font-medium text-gray-900 wrap-break-word">
                                                {{ $voting['voting_name'] }}</p>
                                            <div
                                                class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <div>
                                                    <p
                                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                        {{ __('profile.votings.starts_at') }}</p>
                                                    <p class="mt-1 text-sm text-gray-700">
                                                        {{ \App\Support\LocalizedDateFormatter::date($voting['starts_at']) }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                        {{ __('profile.votings.ends_at') }}</p>
                                                    <p class="mt-1 text-sm text-gray-700">
                                                        {{ \App\Support\LocalizedDateFormatter::date($voting['ends_at']) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>

                                <div
                                    class="mt-3 hidden overflow-x-auto rounded-xl border border-gray-200 md:block">
                                    <table
                                        class="min-w-160 divide-y divide-gray-200 sm:min-w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    {{ __('profile.votings.voting') }}</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    {{ __('profile.votings.starts_at') }}</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    {{ __('profile.votings.ends_at') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @foreach ($missedClosedVotings as $voting)
                                                <tr data-profile-votings-missed-row>
                                                    <td class="px-4 py-3 text-sm text-gray-800">
                                                        {{ $voting['voting_name'] }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">
                                                        {{ \App\Support\LocalizedDateFormatter::date($voting['starts_at']) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">
                                                        {{ \App\Support\LocalizedDateFormatter::date($voting['ends_at']) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if ($activeTab === 'sessions')
                <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6"
                    data-profile-panel="sessions">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ __('profile.sessions.title') }}
                    </h2>

                    @if ($loginSessions === [])
                        <div
                            class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
                            <p class="text-sm text-gray-500">{{ __('profile.sessions.empty') }}
                            </p>
                        </div>
                    @else
                        <div class="mt-4 space-y-3 md:hidden" data-profile-sessions-cards>
                            @foreach ($loginSessions as $session)
                                <article
                                    class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs">
                                    <dl class="space-y-3">
                                        <div>
                                            <dt
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.sessions.logged_in_at') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-800">
                                                {{ \App\Support\LocalizedDateFormatter::dateTime($session['logged_in_at']) }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.sessions.logged_out_at') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-800">
                                                {{ $session['logged_out_at'] ? \App\Support\LocalizedDateFormatter::dateTime($session['logged_out_at']) : __('profile.sessions.active') }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.sessions.duration') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-800">
                                                {{ $session['duration'] ?? __('profile.sessions.in_progress') }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.sessions.ip') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-800 wrap-break-word">
                                                {{ $session['ip_address'] ?? '—' }}</dd>
                                        </div>
                                    </dl>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-4 hidden overflow-x-auto rounded-xl border border-gray-200 md:block"
                            data-profile-sessions-table-wrap>
                            <table class="min-w-3xl divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.sessions.logged_in_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.sessions.logged_out_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.sessions.duration') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.sessions.ip') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($loginSessions as $session)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ \App\Support\LocalizedDateFormatter::dateTime($session['logged_in_at']) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $session['logged_out_at'] ? \App\Support\LocalizedDateFormatter::dateTime($session['logged_out_at']) : __('profile.sessions.active') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $session['duration'] ?? __('profile.sessions.in_progress') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $session['ip_address'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif

            @if ($activeTab === 'received')
                <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6"
                    data-profile-panel="received">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ __('profile.received.title') }}
                    </h2>

                    @if ($receivedMessages->isEmpty())
                        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center"
                            data-profile-received-empty>
                            <p class="text-sm text-gray-500">{{ __('profile.received.empty') }}
                            </p>
                        </div>
                    @else
                        <div class="mt-4 space-y-3 md:hidden" data-profile-received-cards>
                            @foreach ($receivedMessages as $receivedMessage)
                                @php($receivedMessageText = (string) ($receivedMessage['message'] ?? ''))
                                @php($hasLongReceivedMessage = \Illuminate\Support\Str::length($receivedMessageText) > 160)
                                <article
                                    class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs"
                                    data-profile-received-row>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        {{ __('profile.received.subject') }}</p>
                                    <p
                                        class="mt-1 text-sm font-medium text-gray-900 wrap-break-word">
                                        {{ $receivedMessage['subject'] }}</p>

                                    <p
                                        class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        {{ __('profile.received.message') }}</p>
                                    @if (!$hasLongReceivedMessage)
                                        <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                            {{ $receivedMessageText }}</p>
                                    @else
                                        <details class="group mt-1"
                                            data-profile-received-expandable="{{ $receivedMessage['id'] }}">
                                            <summary
                                                class="flex cursor-pointer list-none items-start gap-2 whitespace-pre-line text-sm text-gray-700 marker:content-none"
                                                data-profile-received-toggle="{{ $receivedMessage['id'] }}">
                                                <span class="flex-1 group-open:hidden"
                                                    data-profile-received-preview="{{ $receivedMessage['id'] }}">{{ \Illuminate\Support\Str::limit($receivedMessageText, 160) }}</span>
                                                <span
                                                    class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-gray-200 text-[#793d3d]">
                                                    <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                                                        fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor"
                                                        aria-hidden="true">
                                                        <path stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                    <span
                                                        class="sr-only">{{ __('profile.received.show_more') }}</span>
                                                </span>
                                            </summary>
                                            <p class="mt-2 whitespace-pre-line text-sm text-gray-700"
                                                data-profile-received-full="{{ $receivedMessage['id'] }}">
                                                {{ $receivedMessageText }}</p>
                                        </details>
                                    @endif

                                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.received.sent_at') }}</p>
                                            <p class="mt-1 text-sm text-gray-700">
                                                {{ \App\Support\LocalizedDateFormatter::dateTime($receivedMessage['sent_at']) }}
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.received.status') }}</p>
                                            <p class="mt-1 text-sm text-gray-700">
                                                {{ $receivedMessage['status_label'] }}</p>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-4 hidden overflow-x-auto rounded-xl border border-gray-200 md:block"
                            data-profile-received-table>
                            <table class="min-w-4xl divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.received.subject') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.received.message') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.received.sent_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.received.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach ($receivedMessages as $receivedMessage)
                                        <tr class="border-b border-gray-200 even:bg-gray-50/40 align-top last:border-b-0"
                                            data-profile-received-row>
                                            <td
                                                class="px-4 py-3 text-sm font-medium text-gray-800">
                                                {{ $receivedMessage['subject'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                @php($receivedMessageText = (string) ($receivedMessage['message'] ?? ''))
                                                @php($hasLongReceivedMessage = \Illuminate\Support\Str::length($receivedMessageText) > 160)

                                                @if (!$hasLongReceivedMessage)
                                                    <p class="whitespace-pre-line">
                                                        {{ $receivedMessageText }}</p>
                                                @else
                                                    <details class="group max-w-lg"
                                                        data-profile-received-expandable="{{ $receivedMessage['id'] }}">
                                                        <summary
                                                            class="flex cursor-pointer list-none items-start gap-2 whitespace-pre-line text-gray-700 marker:content-none"
                                                            data-profile-received-toggle="{{ $receivedMessage['id'] }}">
                                                            <span class="flex-1 group-open:hidden"
                                                                data-profile-received-preview="{{ $receivedMessage['id'] }}">{{ \Illuminate\Support\Str::limit($receivedMessageText, 160) }}</span>
                                                            <span
                                                                class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-gray-200 text-[#793d3d]">
                                                                <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="2"
                                                                    stroke="currentColor"
                                                                    aria-hidden="true">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                                </svg>
                                                                <span
                                                                    class="sr-only">{{ __('profile.received.show_more') }}</span>
                                                            </span>
                                                        </summary>
                                                        <p class="mt-2 whitespace-pre-line"
                                                            data-profile-received-full="{{ $receivedMessage['id'] }}">
                                                            {{ $receivedMessageText }}</p>
                                                    </details>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ \App\Support\LocalizedDateFormatter::dateTime($receivedMessage['sent_at']) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $receivedMessage['status_label'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif

            @if ($activeTab === 'messages')
                <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6"
                    data-profile-panel="messages">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ __('profile.messages.title') }}
                    </h2>

                    @if ($userMessages->isEmpty())
                        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center"
                            data-profile-messages-empty>
                            <p class="text-sm text-gray-500">{{ __('profile.messages.empty') }}
                            </p>
                        </div>
                    @else
                        <div class="mt-4 space-y-3 md:hidden" data-profile-messages-cards>
                            @foreach ($userMessages as $userMessage)
                                @php($userMessageText = (string) ($userMessage['message'] ?? ''))
                                @php($hasLongUserMessage = \Illuminate\Support\Str::length($userMessageText) > 160)
                                <article
                                    class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs"
                                    data-profile-message-row>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        {{ __('profile.messages.subject') }}</p>
                                    <p
                                        class="mt-1 text-sm font-medium text-gray-900 wrap-break-word">
                                        {{ $userMessage['subject'] }}</p>

                                    <p
                                        class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        {{ __('profile.messages.message') }}</p>
                                    @if (!$hasLongUserMessage)
                                        <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                            {{ $userMessageText }}</p>
                                    @else
                                        <details class="group mt-1"
                                            data-profile-message-expandable="{{ $userMessage['id'] }}">
                                            <summary
                                                class="flex cursor-pointer list-none items-start gap-2 whitespace-pre-line text-sm text-gray-700 marker:content-none"
                                                data-profile-message-toggle="{{ $userMessage['id'] }}">
                                                <span class="flex-1 group-open:hidden"
                                                    data-profile-message-preview="{{ $userMessage['id'] }}">{{ \Illuminate\Support\Str::limit($userMessageText, 160) }}</span>
                                                <span
                                                    class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-gray-200 text-[#793d3d]">
                                                    <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                                                        fill="none" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor"
                                                        aria-hidden="true">
                                                        <path stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                    <span
                                                        class="sr-only">{{ __('profile.messages.show_more') }}</span>
                                                </span>
                                            </summary>
                                            <p class="mt-2 whitespace-pre-line text-sm text-gray-700"
                                                data-profile-message-full="{{ $userMessage['id'] }}">
                                                {{ $userMessageText }}</p>
                                        </details>
                                    @endif

                                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.messages.sent_at') }}</p>
                                            <p class="mt-1 text-sm text-gray-700">
                                                {{ \App\Support\LocalizedDateFormatter::dateTime($userMessage['created_at']) }}
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ __('profile.messages.status') }}</p>
                                            <p class="mt-1 text-sm text-gray-700">
                                                {{ $userMessage['is_read'] ? __('profile.messages.read') : __('profile.messages.pending') }}
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-4 hidden overflow-x-auto rounded-xl border border-gray-200 md:block"
                            data-profile-messages-table>
                            <table class="min-w-4xl divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.messages.subject') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.messages.message') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.messages.sent_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            {{ __('profile.messages.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach ($userMessages as $userMessage)
                                        <tr class="border-b border-gray-200 even:bg-gray-50/40 align-top last:border-b-0"
                                            data-profile-message-row>
                                            <td
                                                class="px-4 py-3 text-sm font-medium text-gray-800">
                                                {{ $userMessage['subject'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                @php($userMessageText = (string) ($userMessage['message'] ?? ''))
                                                @php($hasLongUserMessage = \Illuminate\Support\Str::length($userMessageText) > 160)

                                                @if (!$hasLongUserMessage)
                                                    <p class="whitespace-pre-line">
                                                        {{ $userMessageText }}</p>
                                                @else
                                                    <details class="group max-w-lg"
                                                        data-profile-message-expandable="{{ $userMessage['id'] }}">
                                                        <summary
                                                            class="flex cursor-pointer list-none items-start gap-2 whitespace-pre-line text-gray-700 marker:content-none"
                                                            data-profile-message-toggle="{{ $userMessage['id'] }}">
                                                            <span class="flex-1 group-open:hidden"
                                                                data-profile-message-preview="{{ $userMessage['id'] }}">{{ \Illuminate\Support\Str::limit($userMessageText, 160) }}</span>
                                                            <span
                                                                class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-gray-200 text-[#793d3d]">
                                                                <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="2"
                                                                    stroke="currentColor"
                                                                    aria-hidden="true">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                                </svg>
                                                                <span
                                                                    class="sr-only">{{ __('profile.messages.show_more') }}</span>
                                                            </span>
                                                        </summary>
                                                        <p class="mt-2 whitespace-pre-line"
                                                            data-profile-message-full="{{ $userMessage['id'] }}">
                                                            {{ $userMessageText }}</p>
                                                    </details>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ \App\Support\LocalizedDateFormatter::dateTime($userMessage['created_at']) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $userMessage['is_read'] ? __('profile.messages.read') : __('profile.messages.pending') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif

            @if ($activeTab === 'owner')
                <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6"
                    data-profile-panel="owner">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ __('profile.owner.title') }}
                    </h2>

                    @if ($owner === null)
                        <p class="mt-3 text-sm text-gray-600">{{ __('profile.owner.no_owner') }}
                        </p>
                    @else
                        <form method="POST"
                            action="{{ route(\App\SupportedLocales::routeName('profile.owner.update')) }}"
                            class="mt-4 space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-4"
                            data-profile-owner-edit-form>
                            @csrf
                            <x-admin.owner-shared-fields mode="http" :owner="$owner" />

                            <div class="mt-2 flex flex-wrap items-center gap-3"
                                data-profile-owner-form-actions>
                                <button type="submit" data-profile-owner-save-button
                                    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#793d3d]">
                                    {{ __('general.buttons.save') }}
                                </button>
                                <a href="{{ route(\App\SupportedLocales::routeName('profile'), ['tab' => 'owner']) }}"
                                    data-profile-owner-cancel-button
                                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:border-brand-600 hover:text-[#793d3d]">
                                    {{ __('general.buttons.cancel') }}
                                </a>
                            </div>
                        </form>

                        <details class="mt-4 rounded-lg border border-zinc-200 bg-gray-50"
                            data-profile-owner-audit-log>
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-zinc-800">
                                <span>{{ __('admin.owners.audit.title') }}</span>
                                <svg class="h-4 w-4 text-zinc-500" fill="none"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </summary>

                            <div class="border-t border-zinc-200 px-4 py-4">
                                @if ($ownerAuditLogs === [])
                                    <div
                                        class="rounded-lg border border-gray-200 bg-white px-4 py-6 text-sm text-gray-500">
                                        {{ __('admin.owners.audit.empty') }}
                                    </div>
                                @else
                                    <div
                                        class="overflow-x-auto [-webkit-overflow-scrolling:touch]">
                                        <table
                                            class="min-w-208 divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white sm:min-w-full">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                        {{ __('admin.owners.audit.date') }}
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                        {{ __('admin.owners.audit.field') }}
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                        {{ __('admin.owners.audit.old_value') }}
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                        {{ __('admin.owners.audit.new_value') }}
                                                    </th>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                        {{ __('admin.owners.audit.changed_by') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white">
                                                @foreach ($ownerAuditLogs as $auditLog)
                                                    <tr data-profile-owner-audit-row>
                                                        <td
                                                            class="px-4 py-2 text-xs text-gray-500">
                                                            {{ $auditLog['changed_at'] }}</td>
                                                        <td
                                                            class="px-4 py-2 text-sm font-medium text-gray-900">
                                                            {{ $auditLog['field_label'] }}</td>
                                                        <td
                                                            class="px-4 py-2 text-sm text-gray-600">
                                                            {{ $auditLog['old_value'] }}</td>
                                                        <td
                                                            class="px-4 py-2 text-sm text-gray-600">
                                                            {{ $auditLog['new_value'] }}</td>
                                                        <td
                                                            class="px-4 py-2 text-xs text-gray-500">
                                                            {{ $auditLog['changed_by'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </details>

                        @if ($activeAssignments->isEmpty())
                            <div
                                class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
                                <p class="text-sm text-gray-500">
                                    {{ __('profile.owner.no_properties') }}</p>
                            </div>
                        @else
                            <form method="POST"
                                action="{{ route(\App\SupportedLocales::routeName('profile.properties.validate')) }}"
                                class="mt-4 space-y-4" data-profile-properties-form>
                                @csrf

                                @error('assignment_ids')
                                    <p
                                        class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700">
                                        {{ $message }}</p>
                                @enderror

                                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                                    data-profile-owner-validation-help>
                                    {{ __('profile.owner.validation_help') }}
                                </div>

                                <div class="grid grid-cols-1 gap-3 md:grid-cols-3"
                                    data-profile-owner-properties-grid>
                                    @foreach ($activeAssignments as $assignment)
                                        <label
                                            class="flex items-start gap-3 rounded-xl border border-gray-200 p-4">
                                            @if (!$assignment->owner_validated)
                                                <input type="checkbox" name="assignment_ids[]"
                                                    value="{{ $assignment->id }}"
                                                    class="mt-0.5 h-6 w-6 rounded border-gray-300 text-[#793d3d] focus:ring-brand-600"
                                                    data-profile-owner-assignment-checkbox>
                                            @endif
                                            <span class="block">
                                                <span
                                                    class="block text-sm font-semibold text-gray-900">{{ $assignment->property ? ($assignment->property->location?->code ? $assignment->property->location->code . '  ' : '') . $assignment->property->name : __('profile.owner.unknown_property') }}</span>
                                                <span
                                                    class="mt-1 block text-sm text-gray-900">{{ __('profile.owner.assignment_dates', ['start' => \App\Support\LocalizedDateFormatter::date($assignment->start_date)]) }}</span>
                                                <span class="mt-1 block text-sm text-gray-900"
                                                    data-profile-owner-property-percentages>
                                                    {{ __('profile.owner.community_pct') }}:
                                                    {{ $assignment->property?->community_pct !== null ? number_format((float) $assignment->property->community_pct, 2, ',', '.') . '%' : '-' }}
                                                    ·
                                                    {{ __('profile.owner.location_pct') }}:
                                                    {{ $assignment->property?->location_pct !== null ? number_format((float) $assignment->property->location_pct, 2, ',', '.') . '%' : '-' }}
                                                </span>
                                                <span
                                                    class="mt-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium {{ $assignment->owner_validated ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $assignment->owner_validated ? __('profile.owner.validated') : __('profile.owner.pending_validation') }}
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @if ($pendingAssignments->isNotEmpty())
                                    <button type="submit"
                                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#793d3d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#5d2e2e]"
                                        @disabled($requiresTermsAcceptance)>
                                        {{ __('profile.owner.validate_selected') }}
                                    </button>
                                @endif
                            </form>
                        @endif
                    @endif
                </section>
            @endif
        </div>
    </div>
</x-layouts::front.main>

<x-layouts::front.main :title="__('profile.title')">
    @push('meta')
        <meta name="description" content="{{ __('profile.seo_description') }}">
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" data-page="profile-page">
        <header
            class="mb-8 rounded-2xl border border-[#d9755b]/25 bg-linear-to-r from-[#edd2c7]/35 via-white to-[#f1bd4d]/15 p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#793d3d]">
                {{ __('profile.kicker') }}</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">
                {{ __('profile.heading') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ __('profile.summary') }}</p>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                data-profile-status>
                {{ session('status') }}
            </div>
        @endif

        @if ($requiresTermsAcceptance)
            <section class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4"
                data-profile-terms-required>
                <h2 class="text-base font-semibold text-amber-900">{{ __('profile.terms.title') }}
                </h2>
                <div class="prose prose-sm mt-3 max-w-none text-amber-900">{!! $termsHtml !!}
                </div>

                <form method="POST"
                    action="{{ route(\App\SupportedLocales::routeName('profile.terms.accept')) }}"
                    class="mt-4">
                    @csrf
                    <button type="submit"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#793d3d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#5d2e2e]">
                        {{ __('profile.terms.accept_button') }}
                    </button>
                </form>
            </section>
        @endif

        <nav class="mb-6 overflow-x-auto" aria-label="{{ __('profile.tabs_aria') }}">
            <ul
                class="flex min-w-max items-center gap-2 rounded-xl border border-gray-200 bg-white p-2">
                @php($tabs = ['overview', 'votings', 'sessions', 'owner'])
                @foreach ($tabs as $tab)
                    <li>
                        <a href="{{ route(\App\SupportedLocales::routeName('profile'), ['tab' => $tab]) }}"
                            class="inline-flex min-h-10 items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ $activeTab === $tab ? 'bg-[#793d3d] text-white' : 'text-gray-700 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                            data-profile-tab="{{ $tab }}">
                            {{ __('profile.tabs.' . $tab) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        @if ($activeTab === 'overview')
            <section class="rounded-2xl border border-gray-200 bg-white p-6"
                data-profile-panel="overview">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('profile.overview.title') }}
                </h2>
                <p class="mt-2 text-sm text-gray-600">{{ __('profile.overview.description') }}</p>

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
                            {{ $loginSessions->count() }}</p>
                    </article>
                    <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('profile.overview.pending_properties') }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ $pendingAssignments->count() }}</p>
                    </article>
                </div>

                <a href="{{ route('security.edit') }}"
                    class="mt-6 inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:border-[#d9755b] hover:text-[#793d3d]">
                    {{ __('profile.overview.change_password') }}
                </a>
            </section>
        @endif

        @if ($activeTab === 'votings')
            <section class="rounded-2xl border border-gray-200 bg-white p-6"
                data-profile-panel="votings">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('profile.votings.title') }}
                </h2>

                @if ($userBallots->isEmpty())
                    <div
                        class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
                        <p class="text-sm text-gray-500">{{ __('profile.votings.empty') }}</p>
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
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
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800">
                                            {{ $ballot['voting_name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $ballot['voted_at']?->format('Y-m-d H:i:s') ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif

        @if ($activeTab === 'sessions')
            <section class="rounded-2xl border border-gray-200 bg-white p-6"
                data-profile-panel="sessions">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('profile.sessions.title') }}
                </h2>

                @if ($loginSessions->isEmpty())
                    <div
                        class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center">
                        <p class="text-sm text-gray-500">{{ __('profile.sessions.empty') }}</p>
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
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
                                            {{ $session['logged_in_at']?->format('Y-m-d H:i:s') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $session['logged_out_at']?->format('Y-m-d H:i:s') ?? __('profile.sessions.active') }}
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

        @if ($activeTab === 'owner')
            <section class="rounded-2xl border border-gray-200 bg-white p-6"
                data-profile-panel="owner">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('profile.owner.title') }}
                </h2>

                @if ($owner === null)
                    <p class="mt-3 text-sm text-gray-600">{{ __('profile.owner.no_owner') }}</p>
                @else
                    <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-sm text-gray-700"><span
                                class="font-semibold">{{ __('profile.owner.primary_name') }}:</span>
                            {{ $owner->coprop1_name }}</p>
                        <p class="mt-1 text-sm text-gray-700"><span
                                class="font-semibold">{{ __('profile.owner.primary_email') }}:</span>
                            {{ $owner->coprop1_email }}</p>
                        <p class="mt-1 text-sm text-gray-700"><span
                                class="font-semibold">{{ __('profile.owner.primary_dni') }}:</span>
                            {{ $owner->coprop1_dni ?: '—' }}</p>
                    </div>

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

                            <div class="space-y-3">
                                @foreach ($activeAssignments as $assignment)
                                    <label
                                        class="flex items-start gap-3 rounded-xl border border-gray-200 p-4">
                                        <input type="checkbox" name="assignment_ids[]"
                                            value="{{ $assignment->id }}"
                                            class="mt-1 h-4 w-4 rounded border-gray-300 text-[#793d3d] focus:ring-[#d9755b]"
                                            @checked(!$assignment->owner_validated)>
                                        <span class="block">
                                            <span
                                                class="block text-sm font-semibold text-gray-900">{{ $assignment->property ? ($assignment->property->location?->code ? $assignment->property->location->code . ' — ' : '') . $assignment->property->name : __('profile.owner.unknown_property') }}</span>
                                            <span
                                                class="mt-1 block text-xs text-gray-600">{{ __('profile.owner.assignment_dates', ['start' => $assignment->start_date?->format('Y-m-d') ?? '—']) }}</span>
                                            <span
                                                class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $assignment->owner_validated ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $assignment->owner_validated ? __('profile.owner.validated') : __('profile.owner.pending_validation') }}
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            <button type="submit"
                                class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#793d3d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#5d2e2e]"
                                @disabled($requiresTermsAcceptance)>
                                {{ __('profile.owner.validate_selected') }}
                            </button>
                        </form>
                    @endif
                @endif
            </section>
        @endif
    </div>
</x-layouts::front.main>

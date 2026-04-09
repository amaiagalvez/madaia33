@props(['title', 'description' => null, 'showStatus' => true])

<div class="flex flex-col gap-6">
    <x-auth.auth-header :title="$title" :description="$description" />

    @if ($showStatus)
        <x-auth.auth-session-status class="text-center" :status="session('status')" />
    @endif

    {{ $slot }}
</div>

<?php

namespace App\Livewire\Concerns;

use App\Models\User;
use App\Models\Campaign;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Jobs\Messaging\DispatchCampaignJob;
use App\Services\Messaging\RecipientResolver;
use App\Actions\Campaigns\DuplicateCampaignAction;
use Illuminate\Support\Facades\Bus;

trait HandlesCampaignManagerActions
{
    public function duplicateCampaign(int $id, DuplicateCampaignAction $duplicateCampaignAction): void
    {
        $this->authorizeViewAny();

        $sourceCampaign = Campaign::query()->with('documents')->findOrFail($id);

        $this->authorize('duplicate', $sourceCampaign);

        $user = $this->currentUser();

        abort_if($user === null, 403);

        $newCampaign = $duplicateCampaignAction->execute($sourceCampaign, $user);

        session()->flash('message', __('general.messages.saved'));

        $this->redirectRoute('admin.campaigns', ['editCampaign' => $newCampaign->id], navigate: true);
    }

    public function sendCampaign(int $id, RecipientResolver $recipientResolver): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        $this->authorize('send', $campaign);

        abort_unless($campaign->status === 'draft', 403);

        if ($recipientResolver->resolve($campaign)->isEmpty()) {
            session()->flash('warning', __('campaigns.admin.no_recipients_warning'));

            return;
        }

        Bus::dispatchSync(new DispatchCampaignJob($campaign->id));
    }

    public function scheduleCampaign(int $id, ?string $scheduledAt = null): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        $this->authorize('send', $campaign);

        abort_unless($campaign->status === 'draft', 403);

        $when = $scheduledAt === null
            ? now()->addMinutes(5)
            : Carbon::parse($scheduledAt);

        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $when,
        ]);
    }

    public function openScheduleModal(int $id): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        $this->authorize('send', $campaign);

        abort_unless($campaign->status === 'draft', 403);

        $this->schedulingCampaignId = $campaign->id;
        $this->scheduleAtInput = now()->addMinutes(5)->setSecond(0)->format('Y-m-d\TH:i');
        $this->showScheduleModal = true;
    }

    public function cancelScheduleModal(): void
    {
        $this->schedulingCampaignId = null;
        $this->scheduleAtInput = '';
        $this->showScheduleModal = false;
    }

    public function saveSchedule(): void
    {
        $this->authorizeViewAny();

        if ($this->schedulingCampaignId === null) {
            return;
        }

        $minimumScheduleAt = now()->startOfMinute();

        $validated = $this->validate(
            [
                'scheduleAtInput' => [
                    'required',
                    'date',
                    'after_or_equal:' . $minimumScheduleAt->format('Y-m-d H:i:s'),
                ],
            ],
            [
                'scheduleAtInput.after_or_equal' => __('campaigns.admin.schedule_modal.after_or_equal'),
            ],
            [
                'scheduleAtInput' => __('campaigns.admin.scheduled_at'),
            ],
        );

        $this->scheduleCampaign($this->schedulingCampaignId, (string) $validated['scheduleAtInput']);
        $this->cancelScheduleModal();
    }

    public function cancelSchedule(int $id): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        $this->authorize('send', $campaign);

        abort_unless($campaign->status === 'scheduled', 403);

        $campaign->update([
            'status' => 'draft',
            'scheduled_at' => null,
        ]);
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        abort_unless($this->canMutateCampaign($campaign), 403);

        $this->confirmingDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->showDeleteModal = false;
    }

    public function confirmAction(int $id, string $action): void
    {
        $this->authorizeViewAny();

        abort_unless(in_array($action, ['duplicate', 'send', 'schedule', 'cancel_schedule'], true), 404);

        $campaign = Campaign::query()->findOrFail($id);

        match ($action) {
            'duplicate' => $this->authorize('duplicate', $campaign),
            'send', 'schedule', 'cancel_schedule' => $this->authorize('send', $campaign),
        };

        if (in_array($action, ['send', 'schedule'], true)) {
            abort_unless($campaign->status === 'draft', 403);
        }

        if ($action === 'cancel_schedule') {
            abort_unless($campaign->status === 'scheduled', 403);
        }

        if ($action === 'schedule') {
            $this->openScheduleModal($campaign->id);

            return;
        }

        $this->confirmingActionId = $campaign->id;
        $this->confirmingAction = $action;
        $this->showActionModal = true;
    }

    public function cancelAction(): void
    {
        $this->confirmingActionId = null;
        $this->confirmingAction = '';
        $this->showActionModal = false;
    }

    public function doAction(
        DuplicateCampaignAction $duplicateCampaignAction,
        RecipientResolver $recipientResolver,
    ): void {
        if ($this->confirmingActionId === null || $this->confirmingAction === '') {
            return;
        }

        $campaignId = $this->confirmingActionId;
        $action = $this->confirmingAction;

        $this->cancelAction();

        match ($action) {
            'duplicate' => $this->duplicateCampaign($campaignId, $duplicateCampaignAction),
            'send' => $this->sendCampaign($campaignId, $recipientResolver),
            'schedule' => $this->scheduleCampaign($campaignId),
            'cancel_schedule' => $this->cancelSchedule($campaignId),
            default => null,
        };
    }

    public function deleteCampaign(): void
    {
        $this->authorizeViewAny();

        if ($this->confirmingDeleteId === null) {
            return;
        }

        $campaign = Campaign::query()->findOrFail($this->confirmingDeleteId);

        abort_unless($this->canMutateCampaign($campaign), 403);

        $campaign->delete();

        $this->cancelDelete();

        session()->flash('message', __('general.messages.deleted'));
    }

    private function upsertCampaign(): Campaign
    {
        if ($this->editingId !== null) {
            $campaign = Campaign::query()->findOrFail($this->editingId);

            abort_unless($this->canMutateCampaign($campaign), 403);

            $campaign->update($this->campaignPayload());

            return $campaign;
        }

        return Campaign::query()->create([
            ...$this->campaignPayload(),
            'created_by_user_id' => $this->currentUser()?->id,
            'status' => 'draft',
            'sent_at' => null,
        ]);
    }

    private function canMutateCampaign(Campaign $campaign): bool
    {
        $user = $this->currentUser();

        return $user !== null && $user->can('update', $campaign);
    }

    private function authorizeViewAny(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        $this->authorize('viewAny', Campaign::class);
    }

    private function authorizeCreate(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        $this->authorize('create', Campaign::class);
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        /** @var User|null $user */
        return $user;
    }
}

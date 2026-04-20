<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Rules\NoScriptTags;
use App\Models\Construction;
use App\Models\ContactMessage;
use App\Support\EmailSiteName;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Actions\Messaging\SendAuthenticatedContactMessageAction;

class PublicConstructionInquiryForm extends Component
{
    public int $constructionId;

    public string $message = '';

    public ?string $statusType = null;

    public string $statusMessage = '';

    public function mount(int $constructionId): void
    {
        $this->constructionId = $constructionId;

        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user !== null, 403);
    }

    /**
     * @return array<string, array<int, NoScriptTags|string>>
     */
    protected function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000', new NoScriptTags],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'message.required' => __('constructions.inquiry.validation.message_required'),
            'message.max' => __('validation.max.string', ['attribute' => __('constructions.inquiry.message'), 'max' => 5000]),
        ];
    }

    public function submit(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $this->validate();

        $construction = Construction::query()
            ->active()
            ->with('tag:id,slug')
            ->findOrFail($this->constructionId);

        abort_unless($construction->tag !== null, 404);

        $ownerSubject = $this->buildOwnerSubject($construction);
        $adminSubject = $this->buildAdminSubject($construction, $ownerSubject);

        $contactMessage = ContactMessage::query()->create([
            'user_id' => Auth::id(),
            'notice_tag_id' => $construction->tag->id,
            'name' => $user->name,
            'email' => $user->email,
            'subject' => $ownerSubject,
            'message' => $this->message,
        ]);

        $emailFailed = app(SendAuthenticatedContactMessageAction::class)->execute(
            user: $user,
            contactMessage: $contactMessage,
            messageBody: $this->message,
            userMailSubject: $ownerSubject,
            adminMailSubject: $adminSubject,
        );

        $this->reset(['message']);
        $this->statusType = $emailFailed ? 'error' : 'success';
        $this->statusMessage = $emailFailed ? __('contact.email_error') : __('constructions.inquiry.success');

        if (! $emailFailed) {
            $this->dispatch('construction-inquiry-submitted', statusType: $this->statusType, statusMessage: $this->statusMessage);
        }
    }

    private function buildOwnerSubject(Construction $construction): string
    {
        return EmailSiteName::resolve() . ' - ' . $construction->title . ' mezua';
    }

    private function buildAdminSubject(Construction $construction, string $ownerSubject): string
    {
        return '[KONTSULA. ' . $construction->title . '] ' . $ownerSubject;
    }

    public function render(): View
    {
        return view('livewire.front.public-construction-inquiry-form');
    }
}

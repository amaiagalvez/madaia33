<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\Construction;
use App\Models\ConstructionInquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConstructionInquiryNotificationMail;

class PublicConstructionInquiryForm extends Component
{
    public int $constructionId;

    public string $subject = '';

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
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'subject.required' => __('constructions.inquiry.validation.subject_required'),
            'message.required' => __('constructions.inquiry.validation.message_required'),
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
            ->with('managers:id,email')
            ->findOrFail($this->constructionId);

        $inquiry = ConstructionInquiry::query()->create([
            'construction_id' => $construction->id,
            'user_id' => Auth::id(),
            'name' => $user->name,
            'email' => $user->email,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        $construction->managers
            ->filter(fn ($manager): bool => filled($manager->email))
            ->each(function ($manager) use ($inquiry, $construction): void {
                Mail::to($manager->email)->send(new ConstructionInquiryNotificationMail($inquiry, $construction));
            });

        $this->reset(['subject', 'message']);
        $this->statusType = 'success';
        $this->statusMessage = __('constructions.inquiry.success');
    }

    public function render(): View
    {
        return view('livewire.front.public-construction-inquiry-form');
    }
}

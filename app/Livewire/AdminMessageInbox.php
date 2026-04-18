<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\MessageReply;
use Livewire\WithPagination;
use App\Mail\MessageReplyMail;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminMessageInbox extends Component
{
    use WithPagination;

    public ?int $openMessageId = null;

    public ?int $confirmingDeleteId = null;

    public bool $showDeleteModal = false;

    public ?int $confirmingReadId = null;

    public string $readAction = '';

    public bool $showReadModal = false;

    public ?int $replyingMessageId = null;

    public string $replyBody = '';

    public bool $showReplyModal = false;

    public string $sortColumn = 'created_at';

    public string $sortDir = 'desc';

    public string $search = '';

    public string $readFilter = 'all';

    public function mount(): void
    {
        abort_unless($this->canManageInbox(), 403);
    }

    public function openMessage(int $id): void
    {
        $this->openMessageId = ($this->openMessageId === $id) ? null : $id;

        if ($this->openMessageId !== null) {
            $message = ContactMessage::find($id);
            if ($message && ! $message->is_read) {
                $message->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            }
        }
    }

    public function toggleRead(int $id): void
    {
        $message = ContactMessage::findOrFail($id);

        if ($message->is_read) {
            $message->update(['is_read' => false, 'read_at' => null]);
        } else {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function confirmReadToggle(int $id, bool $markRead): void
    {
        $this->confirmingReadId = $id;
        $this->readAction = $markRead ? 'read' : 'unread';
        $this->showReadModal = true;
    }

    public function doReadToggle(): void
    {
        if ($this->confirmingReadId === null) {
            return;
        }

        $this->toggleRead($this->confirmingReadId);
        $this->cancelReadToggle();
    }

    public function cancelReadToggle(): void
    {
        $this->confirmingReadId = null;
        $this->readAction = '';
        $this->showReadModal = false;
    }

    public function openReplyModal(int $messageId): void
    {
        $message = ContactMessage::query()
            ->with('reply')
            ->findOrFail($messageId);

        if ($message->reply?->sent_at !== null) {
            return;
        }

        $this->replyingMessageId = $messageId;
        $this->replyBody = $message->reply?->reply_body ?? '';
        $this->showReplyModal = true;
    }

    public function cancelReply(): void
    {
        $this->replyingMessageId = null;
        $this->replyBody = '';
        $this->showReplyModal = false;
    }

    public function sendReply(): void
    {
        $this->validate([
            'replyBody' => 'required|string|min:10|max:5000',
        ], [
            'replyBody.required' => __('validation.required', ['attribute' => __('contact.admin.reply_body')]),
            'replyBody.min' => __('validation.min.string', ['attribute' => __('contact.admin.reply_body'), 'min' => 10]),
            'replyBody.max' => __('validation.max.string', ['attribute' => __('contact.admin.reply_body'), 'max' => 5000]),
        ]);

        if ($this->replyingMessageId === null) {
            return;
        }

        $message = ContactMessage::query()
            ->with('reply')
            ->findOrFail($this->replyingMessageId);

        if ($message->reply?->sent_at !== null) {
            $this->cancelReply();

            return;
        }

        $reply = $message->reply ?? new MessageReply([
            'contact_message_id' => $message->id,
        ]);
        $reply->reply_body = $this->replyBody;
        $reply->sent_at = null;
        $reply->save();

        Mail::to($message->email)->send(new MessageReplyMail($reply));

        $reply->forceFill([
            'sent_at' => now(),
        ])->save();

        $this->cancelReply();
        $this->openMessageId = null;
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDir = 'desc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setReadFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'read', 'unread'], true)) {
            return;
        }

        $this->readFilter = $filter;
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        abort_unless($this->canDeleteMessages(), 403);

        $this->confirmingDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->showDeleteModal = false;
    }

    public function deleteMessage(): void
    {
        abort_unless($this->canDeleteMessages(), 403);

        if ($this->confirmingDeleteId) {
            ContactMessage::findOrFail($this->confirmingDeleteId)->delete();

            if ($this->openMessageId === $this->confirmingDeleteId) {
                $this->openMessageId = null;
            }

            $this->confirmingDeleteId = null;
            $this->showDeleteModal = false;
        }
    }

    /**
     * @return LengthAwarePaginator<int, ContactMessage>
     */
    public function getMessagesProperty(): LengthAwarePaginator
    {
        $allowedSortColumns = ['created_at', 'is_read'];
        $sortBy = in_array($this->sortColumn, $allowedSortColumns) ? $this->sortColumn : 'created_at';
        $sortDir = in_array($this->sortDir, ['asc', 'desc']) ? $this->sortDir : 'desc';

        return ContactMessage::query()
            ->with('reply')
            ->when($this->readFilter === 'read', fn($query) => $query->where('is_read', true))
            ->when($this->readFilter === 'unread', fn($query) => $query->where('is_read', false))
            ->when(trim($this->search) !== '', function ($query): void {
                $term = '%' . trim($this->search) . '%';

                $query->where(function ($innerQuery) use ($term): void {
                    $innerQuery
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('subject', 'like', $term)
                        ->orWhere('message', 'like', $term);
                });
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate(15);
    }

    public function render(): View
    {
        abort_unless($this->canManageInbox(), 403);

        return view('livewire.admin.message-inbox', [
            'messages' => $this->getMessagesProperty(),
            'canDeleteMessages' => $this->canDeleteMessages(),
        ]);
    }

    private function canManageInbox(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasAnyRole([Role::SUPER_ADMIN, Role::GENERAL_ADMIN]) ?? false;
    }

    private function canDeleteMessages(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasRole(Role::SUPER_ADMIN) ?? false;
    }
}

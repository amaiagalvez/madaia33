<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class AdminMessageInbox extends Component
{
    public ?int $openMessageId = null;

    public ?int $confirmingDeleteId = null;

    public bool $showDeleteModal = false;

    public string $sortBy = 'created_at';

    public string $sortDir = 'desc';

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

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'desc';
        }
    }

    public function confirmDelete(int $id): void
    {
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
     * @return Collection<int, ContactMessage>
     */
    public function getMessagesProperty(): Collection
    {
        $allowedSortColumns = ['created_at', 'is_read'];
        $sortBy = in_array($this->sortBy, $allowedSortColumns) ? $this->sortBy : 'created_at';
        $sortDir = in_array($this->sortDir, ['asc', 'desc']) ? $this->sortDir : 'desc';

        return ContactMessage::orderBy($sortBy, $sortDir)->get();
    }

    public function render(): View
    {
        return view('livewire.admin.message-inbox', [
            'messages' => $this->getMessagesProperty(),
        ]);
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
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

    public string $sortBy = 'created_at';

    public string $sortDir = 'desc';

    public string $search = '';

    public string $readFilter = 'all';

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

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
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
     * @return LengthAwarePaginator<int, ContactMessage>
     */
    public function getMessagesProperty(): LengthAwarePaginator
    {
        $allowedSortColumns = ['created_at', 'is_read'];
        $sortBy = in_array($this->sortBy, $allowedSortColumns) ? $this->sortBy : 'created_at';
        $sortDir = in_array($this->sortDir, ['asc', 'desc']) ? $this->sortDir : 'desc';

        return ContactMessage::query()
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
        return view('livewire.admin.message-inbox', [
            'messages' => $this->getMessagesProperty(),
        ]);
    }
}

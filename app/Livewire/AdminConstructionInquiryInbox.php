<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Construction;
use Livewire\WithPagination;
use App\Models\ConstructionInquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Builder;
use App\Mail\ConstructionInquiryReplyMail;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminConstructionInquiryInbox extends Component
{
  use WithPagination;

  public ?int $openInquiryId = null;

  public ?int $replyingInquiryId = null;

  public string $replyBody = '';

  public bool $showReplyModal = false;

  public string $constructionFilter = 'all';

  public function mount(): void
  {
    abort_unless($this->canManageInbox(), 403);
  }

  public function openInquiry(int $id): void
  {
    $inquiry = $this->visibleInquiriesQuery()->findOrFail($id);

    $this->openInquiryId = $this->openInquiryId === $id ? null : $id;

    if ($this->openInquiryId !== null && ! $inquiry->is_read) {
      $inquiry->update([
        'is_read' => true,
        'read_at' => now(),
      ]);
    }
  }

  public function toggleRead(int $id): void
  {
    $inquiry = $this->visibleInquiriesQuery()->findOrFail($id);

    $inquiry->update([
      'is_read' => ! $inquiry->is_read,
      'read_at' => $inquiry->is_read ? null : now(),
    ]);
  }

  public function setConstructionFilter(string $constructionId): void
  {
    $this->constructionFilter = $constructionId;
    $this->resetPage();
  }

  public function openReplyModal(int $inquiryId): void
  {
    $inquiry = $this->visibleInquiriesQuery()->findOrFail($inquiryId);

    $this->replyingInquiryId = $inquiry->id;
    $this->replyBody = $inquiry->reply ?? '';
    $this->showReplyModal = true;
  }

  public function cancelReply(): void
  {
    $this->replyingInquiryId = null;
    $this->replyBody = '';
    $this->showReplyModal = false;
  }

  public function sendReply(): void
  {
    $this->validate([
      'replyBody' => 'required|string|min:10|max:5000',
    ], [
      'replyBody.required' => __('validation.required', ['attribute' => __('admin.construction_inquiries.reply_body')]),
      'replyBody.min' => __('validation.min.string', ['attribute' => __('admin.construction_inquiries.reply_body'), 'min' => 10]),
      'replyBody.max' => __('validation.max.string', ['attribute' => __('admin.construction_inquiries.reply_body'), 'max' => 5000]),
    ]);

    if ($this->replyingInquiryId === null) {
      return;
    }

    $inquiry = $this->visibleInquiriesQuery()->findOrFail($this->replyingInquiryId);

    $inquiry->update([
      'reply' => $this->replyBody,
      'replied_at' => now(),
    ]);

    Mail::to($inquiry->email)->send(new ConstructionInquiryReplyMail($inquiry->fresh(['construction'])));

    $this->cancelReply();
  }

  /**
   * @return LengthAwarePaginator<int, ConstructionInquiry>
   */
  public function getInquiriesProperty(): LengthAwarePaginator
  {
    return $this->visibleInquiriesQuery()
      ->with('construction')
      ->when($this->constructionFilter !== 'all', function (Builder $query): void {
        $query->where('construction_id', (int) $this->constructionFilter);
      })
      ->orderByDesc('created_at')
      ->paginate(15);
  }

  public function render(): View
  {
    abort_unless($this->canManageInbox(), 403);

    return view('livewire.admin.construction-inquiry-inbox', [
      'constructionOptions' => $this->constructionOptions(),
      'inquiries' => $this->getInquiriesProperty(),
    ]);
  }

  /**
   * @return Builder<ConstructionInquiry>
   */
  private function visibleInquiriesQuery(): Builder
  {
    $query = ConstructionInquiry::query();

    if ($this->canSeeAllInquiries()) {
      return $query;
    }

    /** @var User $user */
    $user = Auth::user();

    return $query->whereHas('construction.managers', function (Builder $query) use ($user): void {
      $query->whereKey($user->id);
    });
  }

  /**
   * @return array<int, string>
   */
  private function constructionOptions(): array
  {
    $query = Construction::query()->orderBy('title');

    if (! $this->canSeeAllInquiries()) {
      /** @var User $user */
      $user = Auth::user();
      $query->whereHas('managers', function (Builder $query) use ($user): void {
        $query->whereKey($user->id);
      });
    }

    return $query->pluck('title', 'id')->all();
  }

  private function canManageInbox(): bool
  {
    /** @var User|null $user */
    $user = Auth::user();

    return $user?->canManageConstructions() ?? false;
  }

  private function canSeeAllInquiries(): bool
  {
    /** @var User|null $user */
    $user = Auth::user();

    return $user?->hasAnyRole([Role::SUPER_ADMIN, Role::GENERAL_ADMIN]) ?? false;
  }
}

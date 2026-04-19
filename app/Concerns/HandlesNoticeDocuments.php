<?php

namespace App\Concerns;

use App\Models\Notice;
use App\Models\NoticeDocument;

trait HandlesNoticeDocuments
{
    public function uploadDocument(int $id): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $this->validateOnly('attachments');
        $this->validateOnly('attachments.*');

        $notice = Notice::query()->findOrFail($id);
        $this->storeAttachments($notice);
        $this->setStoredDocumentsFromNotice($notice->load(['documents' => fn ($query) => $query->withCount('downloads')]));
    }

    public function removeDocument(int $id): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $document = NoticeDocument::query()->findOrFail($id);
        $notice = $document->notice;

        $document->delete();

        if ($notice !== null) {
            $this->setStoredDocumentsFromNotice($notice->load(['documents' => fn ($query) => $query->withCount('downloads')]));
        }
    }

    public function toggleDocumentPublic(int $id): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $document = NoticeDocument::query()->findOrFail($id);
        $notice = $document->notice;

        $document->update([
            'is_public' => ! $document->is_public,
        ]);

        if ($notice !== null) {
            $this->setStoredDocumentsFromNotice($notice->load(['documents' => fn ($query) => $query->withCount('downloads')]));
        }
    }

    private function storeAttachments(Notice $notice): void
    {
        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('notice-documents/' . $notice->id, 'public');

            NoticeDocument::query()->create([
                'notice_id' => $notice->id,
                'filename' => $attachment->getClientOriginalName(),
                'path' => $path,
                'mime_type' => (string) $attachment->getClientMimeType(),
                'size_bytes' => (int) $attachment->getSize(),
                'is_public' => false,
            ]);
        }

        $this->attachments = [];
    }

    private function setStoredDocumentsFromNotice(Notice $notice): void
    {
        $this->storedDocuments = $notice->documents
            ->map(fn (NoticeDocument $document): array => [
                'id' => $document->id,
                'filename' => $document->filename,
                'is_public' => (bool) $document->is_public,
                'downloads_count' => (int) $document->downloads_count,
            ])
            ->values()
            ->all();
    }
}

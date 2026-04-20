<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticeRead;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

class NoticeReadController extends Controller
{
    public function store(Request $request, Notice $notice): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['ok' => false], 401);
        }

        $owner = $user->owner;

        if ($owner === null) {
            return response()->json(['ok' => false], 403);
        }

        $noticeRead = NoticeRead::withTrashed()->firstOrNew([
            'notice_id' => $notice->id,
            'owner_id' => $owner->id,
        ]);

        if (! $noticeRead->exists || $noticeRead->trashed()) {
            $noticeRead->user_id = $user->id;
            $noticeRead->ip_address = $request->ip();
            $noticeRead->opened_at = Carbon::now();
            $noticeRead->deleted_at = null;
            $noticeRead->save();
        }

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\Service;
use App\Models\ContentBlock;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $serviceDrafts = Service::where(fn ($query) => $query->whereNull('published_revision_id')->orWhereColumn('current_revision_id', '!=', 'published_revision_id'))->count();
        $contentDrafts = ContentBlock::where(fn ($query) => $query->whereNull('published_revision_id')->orWhereColumn('current_revision_id', '!=', 'published_revision_id'))->count();
        return view('admin.dashboard', [
            'total' => Service::count(),
            'published' => Service::whereNotNull('published_revision_id')->count(),
            'drafts' => $serviceDrafts + $contentDrafts,
            'pendingReviews' => \App\Models\ReviewRequest::where('status', 'pending')->count(),
            'events' => AuditEvent::with(['user', 'service.currentRevision'])->latest('id')->limit(8)->get(),
        ]);
    }
}

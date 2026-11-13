<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\Service;
use App\Models\ContentBlock;

class DashboardController extends Controller
{
    public function __invoke(\Illuminate\Http\Request $request, \App\Services\DashboardTasks $tasks)
    {
        $serviceDrafts = Service::where(fn ($query) => $query->whereNull('published_revision_id')->orWhereColumn('current_revision_id', '!=', 'published_revision_id'))->count();
        $contentDrafts = ContentBlock::where(fn ($query) => $query->whereNull('published_revision_id')->orWhereColumn('current_revision_id', '!=', 'published_revision_id'))->count();
        return view('admin.dashboard', [
            'total' => Service::count(),
            'published' => Service::whereHas('publishedRevision', fn ($query) => $query->where('payload->visible', true))->count(),
            'hidden' => Service::whereHas('publishedRevision', fn ($query) => $query->where('payload->visible', false))->count(),
            'drafts' => $serviceDrafts + $contentDrafts,
            'tasks' => $tasks->forUser($request->user()),
            'events' => AuditEvent::with(['user', 'service.currentRevision'])->latest('id')->limit(8)->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\Service;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'total' => Service::count(),
            'published' => Service::whereNotNull('published_revision_id')->count(),
            'drafts' => Service::where(fn ($query) => $query->whereNull('published_revision_id')->orWhereColumn('current_revision_id', '!=', 'published_revision_id'))->count(),
            'events' => AuditEvent::with(['user', 'service.currentRevision'])->latest('id')->limit(8)->get(),
        ]);
    }
}

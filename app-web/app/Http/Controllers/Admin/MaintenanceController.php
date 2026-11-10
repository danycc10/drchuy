<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{BackupRun, ContentBlock, MediaAsset, Service, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MaintenanceController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return view('admin.maintenance', [
            'runs' => BackupRun::latest('id')->paginate(10),
            'lastBackup' => BackupRun::where('status', 'complete')->latest('id')->first(),
            'lastRestore' => BackupRun::whereNotNull('restore_checked_at')->latest('restore_checked_at')->first(),
        ]);
    }

    public function export()
    {
        Gate::authorize('viewAny', User::class);
        $data = DB::transaction(fn () => [
            'format' => 'medicina-respiratoria-content-v1', 'exported_at' => now()->toIso8601String(),
            'services' => Service::with('revisions')->get()->map(fn ($item) => [
                'source_id' => $item->source_id, 'category' => $item->category->name,
                'published_revision' => $item->published_revision_id, 'current_revision' => $item->current_revision_id,
                'revisions' => $item->revisions->map(fn ($revision) => ['id' => $revision->id, 'payload' => $revision->payload, 'created_at' => $revision->created_at]),
            ]),
            'content' => ContentBlock::with('revisions')->get()->map(fn ($item) => [
                'key' => $item->key, 'published_revision' => $item->published_revision_id, 'current_revision' => $item->current_revision_id,
                'revisions' => $item->revisions->map(fn ($revision) => ['id' => $revision->id, 'payload' => $revision->payload, 'created_at' => $revision->created_at]),
            ]),
            'images' => MediaAsset::get(['id', 'title', 'width', 'height']),
        ]);

        return response()->streamDownload(fn () => print(json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            'contenido-'.now('America/Matamoros')->format('Y-m-d').'.json', ['Content-Type' => 'application/json; charset=UTF-8']);
    }
}

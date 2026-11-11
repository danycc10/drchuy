<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ContentBlock, Service};
use App\Services\RevisionComparison;
use Illuminate\Support\Facades\Gate;

class ComparisonController extends Controller
{
    public function service(Service $service, RevisionComparison $comparison)
    {
        return $this->show($service, $comparison);
    }

    public function content(ContentBlock $block, RevisionComparison $comparison)
    {
        return $this->show($block, $comparison);
    }

    private function show(Service|ContentBlock $target, RevisionComparison $comparison)
    {
        Gate::authorize('update', $target);
        $target->load(['currentRevision', 'publishedRevision']);
        $base = $target->publishedRevision;
        $revision = $target->currentRevision;
        $changes = $comparison->changes($target, $base, $revision);

        return view('admin.comparison', compact('target', 'base', 'revision', 'changes'));
    }
}

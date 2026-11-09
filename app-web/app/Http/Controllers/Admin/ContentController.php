<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\ContentRevision;
use App\Models\MediaAsset;
use App\Services\ContentPublisher;
use App\Support\ContentSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContentController extends Controller
{
    public function index()
    {
        return view('admin.content.index', ['blocks' => ContentBlock::with('currentRevision')->get(), 'schemas' => ContentSchema::definitions()]);
    }

    public function edit(ContentBlock $block)
    {
        Gate::authorize('update', $block);
        $block->load('currentRevision');

        return view('admin.content.edit', [
            'block' => $block, 'schema' => ContentSchema::definitions()[$block->key],
            'revisions' => $block->revisions()->with('author')->latest('id')->paginate(6),
            'mediaOptions' => $block->key === 'media' ? MediaAsset::orderBy('title')->get() : collect(),
        ]);
    }

    public function update(Request $request, ContentBlock $block, ContentPublisher $publisher)
    {
        Gate::authorize('update', $block);
        $data = $request->validate(['revision_id' => 'required|integer|min:1', 'payload' => 'required|array']);
        if ($block->key === 'hours') {
            $data['payload']['exceptions'] ??= [];
        }
        $publisher->draft($block, $data['payload'], (int) $data['revision_id'], $request->user());

        return to_route('admin.content.edit', $block)->with('status', 'Borrador guardado. Revísalo antes de publicar.');
    }

    public function publish(Request $request, ContentBlock $block, ContentPublisher $publisher)
    {
        $data = $request->validate(['revision_id' => 'required|integer|min:1']);
        $publisher->publish($block, (int) $data['revision_id'], $request->user());

        return to_route('admin.content.edit', $block)->with('status', 'Contenido publicado en el sitio.');
    }

    public function revision(ContentBlock $block, ContentRevision $revision)
    {
        Gate::authorize('update', $block);
        abort_unless($revision->content_block_id === $block->id, 404);

        return view('admin.content.revision', ['block' => $block, 'revision' => $revision, 'schema' => ContentSchema::definitions()[$block->key], 'mediaOptions' => $revision->mediaAssets->keyBy('id')]);
    }

    public function restore(Request $request, ContentBlock $block, ContentRevision $revision, ContentPublisher $publisher)
    {
        $data = $request->validate(['revision_id' => 'required|integer|min:1']);
        $publisher->draft($block, [], (int) $data['revision_id'], $request->user(), $revision);

        return to_route('admin.content.edit', $block)->with('status', 'Versión recuperada como un nuevo borrador.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ContentBlock, ReviewRequest, Service};
use App\Services\EditorialReview;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['status' => ['nullable', Rule::in(array_keys(ReviewRequest::statuses()))]]);
        $status = $data['status'] ?? 'pending';
        $reviews = ReviewRequest::with(['service.currentRevision', 'contentBlock', 'requester', 'reviewer'])
            ->where('status', $status)->latest('id')->paginate(15)->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'status'));
    }

    public function store(Request $request, EditorialReview $workflow)
    {
        $data = $request->validate(['type' => 'required|in:service,content', 'target_id' => 'required|integer', 'revision_id' => 'required|integer', 'note' => 'nullable|string|max:1500']);
        $target = ($data['type'] === 'service' ? Service::class : ContentBlock::class)::findOrFail($data['target_id']);
        $review = $workflow->submit($target, (int) $data['revision_id'], $data['note'] ?? null, $request->user());

        return to_route('admin.reviews.show', $review)->with('status', 'Solicitud registrada. El responsable puede revisarla desde esta bandeja.');
    }

    public function show(ReviewRequest $review)
    {
        $target = $review->target();
        $revision = $target->revisions()->findOrFail($review->revision_id);
        $base = $review->base_revision_id ? $target->revisions()->findOrFail($review->base_revision_id) : null;
        $before = Arr::dot($base?->payload ?? []);
        $after = Arr::dot($revision->payload);
        $changes = [];
        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changes[] = ['field' => $field, 'before' => $this->display($before[$field] ?? null), 'after' => $this->display($after[$field] ?? null)];
            }
        }

        return view('admin.reviews.show', compact('review', 'target', 'revision', 'base', 'changes'));
    }

    public function decide(Request $request, ReviewRequest $review, EditorialReview $workflow)
    {
        $data = $request->validate(['decision' => 'required|in:publish,changes', 'response' => 'required_if:decision,changes|nullable|string|max:1500']);
        $workflow->decide($review, $request->user(), $data['decision'], $data['response'] ?? null);

        return back()->with('status', $data['decision'] === 'publish' ? 'Versión revisada y publicada.' : 'Cambios solicitados. El comentario quedó disponible para el equipo.');
    }

    private function display(mixed $value): string
    {
        return match (true) { $value === null => '—', is_bool($value) => $value ? 'Sí' : 'No', is_array($value) => 'Sin elementos', default => (string) $value };
    }
}

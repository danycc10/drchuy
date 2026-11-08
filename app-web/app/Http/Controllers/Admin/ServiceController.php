<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRevision;
use App\Services\ServicePublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'category' => 'nullable|integer|exists:service_categories,id',
            'status' => ['nullable', Rule::in(['draft', 'published'])],
        ]);
        $query = Service::with(['category', 'currentRevision', 'publishedRevision'])->orderBy('source_id');
        if ($filters['q'] ?? null) {
            $query->whereHas('currentRevision', fn ($q) => $q->where('payload->es->name', 'like', '%'.$filters['q'].'%')->orWhere('payload->en->name', 'like', '%'.$filters['q'].'%'));
        }
        if ($filters['category'] ?? null) {
            $query->where('service_category_id', $filters['category']);
        }
        if (($filters['status'] ?? null) === 'draft') {
            $query->where(fn ($q) => $q->whereNull('published_revision_id')->orWhereColumn('current_revision_id', '!=', 'published_revision_id'));
        } elseif (($filters['status'] ?? null) === 'published') {
            $query->whereNotNull('published_revision_id');
        }

        return view('admin.services.index', ['services' => $query->paginate(10)->withQueryString(), 'categories' => ServiceCategory::orderBy('position')->get()]);
    }

    public function edit(Service $service)
    {
        Gate::authorize('update', $service);
        $service->load(['category', 'currentRevision', 'publishedRevision']);

        return view('admin.services.edit', ['service' => $service, 'revisions' => $service->revisions()->with('author')->latest('id')->paginate(6)]);
    }

    public function update(SaveServiceRequest $request, Service $service, ServicePublisher $publisher)
    {
        $data = $request->validated();
        $payload = ['es' => $data['es'], 'en' => $data['en'], 'visible' => $request->boolean('visible'), 'position' => (int) $data['position']];
        $publisher->draft($service, $payload, (int) $data['revision_id'], $request->user());

        return to_route('admin.services.edit', $service)->with('status', 'Borrador guardado. La versión pública sigue igual hasta que se publique este cambio.');
    }

    public function publish(Request $request, Service $service, ServicePublisher $publisher)
    {
        $data = $request->validate(['revision_id' => 'required|integer|min:1']);
        $publisher->publish($service, (int) $data['revision_id'], $request->user());

        return to_route('admin.services.edit', $service)->with('status', 'Versión publicada. El sitio ya utiliza este contenido y su visibilidad seleccionada.');
    }

    public function revision(Service $service, ServiceRevision $revision)
    {
        Gate::authorize('update', $service);
        abort_unless($revision->service_id === $service->id, 404);

        return view('admin.services.revision', compact('service', 'revision'));
    }

    public function restore(Request $request, Service $service, ServiceRevision $revision, ServicePublisher $publisher)
    {
        $data = $request->validate(['revision_id' => 'required|integer|min:1']);
        $publisher->draft($service, [], (int) $data['revision_id'], $request->user(), $revision);

        return to_route('admin.services.edit', $service)->with('status', 'Versión recuperada como un nuevo borrador. Revísala antes de publicar.');
    }
}

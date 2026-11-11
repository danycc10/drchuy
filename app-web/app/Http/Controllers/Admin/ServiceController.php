<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRevision;
use App\Models\MediaAsset;
use App\Services\ServicePublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'category' => 'nullable|integer|exists:service_categories,id',
            'status' => ['nullable', Rule::in(['draft', 'published', 'hidden'])],
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
            $query->whereHas('publishedRevision', fn ($q) => $q->where('payload->visible', true));
        } elseif (($filters['status'] ?? null) === 'hidden') {
            $query->whereHas('publishedRevision', fn ($q) => $q->where('payload->visible', false));
        }

        return view('admin.services.index', ['services' => $query->paginate(10)->withQueryString(), 'categories' => ServiceCategory::orderBy('position')->get()]);
    }

    public function edit(Service $service)
    {
        Gate::authorize('update', $service);
        $service->load(['category', 'currentRevision', 'publishedRevision']);

        return view('admin.services.edit', ['service' => $service, 'revisions' => $service->revisions()->with('author')->latest('id')->paginate(6), 'mediaOptions' => MediaAsset::orderBy('title')->get()]);
    }

    public function order(Request $request)
    {
        $data = $request->validate(['category' => 'nullable|integer|exists:service_categories,id']);
        $categories = ServiceCategory::orderBy('position')->get();
        $selected = $categories->firstWhere('id', $data['category'] ?? $categories->first()?->id);
        $services = Service::with(['currentRevision', 'publishedRevision'])->where('service_category_id', $selected?->id)->get()
            ->sortBy(fn ($service) => [$service->currentRevision->payload['position'], $service->id])->values();
        foreach ($services as $service) { Gate::authorize('update', $service); }

        return view('admin.services.order', compact('categories', 'selected', 'services'));
    }

    public function reorder(Request $request, ServicePublisher $publisher)
    {
        $data = $request->validate([
            'category' => 'required|integer|exists:service_categories,id',
            'order' => 'required|array|list|min:1|max:999', 'order.*' => 'required|integer|distinct',
            'revisions' => 'required|array|max:999', 'revisions.*' => 'required|integer|min:1',
        ]);
        DB::transaction(function () use ($data, $request, $publisher) {
            app(\App\Services\AccountSecurity::class)->lockActor($request->user());
            $services = Service::where('service_category_id', $data['category'])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $ids = array_map('intval', $data['order']);
            $expected = $services->keys()->all();
            $sorted = $ids; sort($sorted); sort($expected);
            if ($sorted !== $expected) {
                throw ValidationException::withMessages(['order' => 'La lista cambió. Recarga para ordenar todos los servicios de esta categoría.']);
            }
            foreach ($services as $service) {
                Gate::authorize('update', $service);
                if ((int) ($data['revisions'][$service->id] ?? 0) !== $service->current_revision_id) {
                    throw ValidationException::withMessages(['order' => 'Otra persona editó un servicio. Recarga la lista antes de guardar el orden.']);
                }
            }
            foreach ($ids as $index => $id) {
                $service = $services[$id];
                $payload = $service->currentRevision->payload;
                if ((int) $payload['position'] !== $index + 1) {
                    $payload['position'] = $index + 1;
                    $publisher->draft($service, $payload, $service->current_revision_id, $request->user());
                }
            }
        });

        return to_route('admin.services.order', ['category' => $data['category']])->with('status', 'Orden guardado en los borradores. Revisa y publica los servicios modificados para actualizar el catálogo público.');
    }

    public function update(SaveServiceRequest $request, Service $service, ServicePublisher $publisher)
    {
        $data = $request->validated();
        $payload = ['es' => $data['es'], 'en' => $data['en'], 'visible' => $request->boolean('visible'), 'position' => (int) $data['position']];
        if (array_key_exists('image_id', $data)) { $payload['image_id'] = $data['image_id']; }
        if (isset($data['image_position'])) { $payload['image_position'] = array_map('intval', $data['image_position']); }
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

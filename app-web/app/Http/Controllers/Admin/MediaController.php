<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\MediaAsset;
use App\Services\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', MediaAsset::class);
        $filters = $request->validate(['q' => 'nullable|string|max:100', 'usage' => 'nullable|in:used,unused']);
        $query = MediaAsset::with('author')->withCount(['revisions', 'serviceRevisions'])->latest('id');
        if (filled($filters['q'] ?? null)) {
            $query->where(fn ($query) => $query->where('title', 'like', '%'.$filters['q'].'%')->orWhere('original_name', 'like', '%'.$filters['q'].'%'));
        }
        if (($filters['usage'] ?? '') === 'used') {
            $query->where(fn ($query) => $query->has('revisions')->orHas('serviceRevisions'));
        } elseif (($filters['usage'] ?? '') === 'unused') {
            $query->doesntHave('revisions')->doesntHave('serviceRevisions');
        }

        return view('admin.media.index', [
            'assets' => $query->paginate(12)->withQueryString(),
            'mediaBlock' => ContentBlock::where('key', 'media')->first(),
        ]);
    }

    public function store(Request $request, MediaLibrary $library)
    {
        Gate::authorize('create', MediaAsset::class);
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'image' => 'required|file|mimetypes:image/jpeg,image/png,image/webp|max:'.config('media.max_upload_kb'),
        ], [
            'image.required' => 'Selecciona una fotografía.',
            'image.uploaded' => 'La carga no se completó. Revisa el tamaño del archivo y los límites del servidor.',
            'image.mimetypes' => 'Utiliza una fotografía JPG, PNG o WebP.',
            'image.max' => 'La fotografía debe pesar como máximo 8 MB.',
        ]);
        try {
            $asset = $library->upload($request->file('image'), $data['title'], $request->user());
        } catch (ValidationException|\Illuminate\Auth\Access\AuthorizationException|\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'No se pudo guardar la imagen. Intenta de nuevo.'], 500);
            }

            return back()->withInput($request->only('title'))->withErrors([
                'image' => 'No se pudo guardar la fotografía. Inténtalo de nuevo; si continúa, revisa el espacio disponible y los permisos del servidor.',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['id' => $asset->id, 'title' => $asset->title, 'url' => $asset->url(true), 'thumb' => $asset->url(true, 'thumb')], 201);
        }

        return to_route('admin.media.index')->with('status', 'Imagen guardada en la biblioteca. Puedes seleccionarla al editar un servicio o en «Fotografías del sitio». Al guardar ese editor, el sitio mostrará la imagen.');
    }

    public function image(MediaAsset $media, string $variant)
    {
        Gate::authorize('view', $media);
        abort_unless(in_array($variant, ['display', 'thumb'], true), 404);

        return $this->imageResponse($media, $variant, false);
    }

    public function publicImage(MediaAsset $media, string $variant)
    {
        abort_unless(in_array($variant, ['display', 'thumb'], true) && $media->isPublished(), 404);

        return $this->imageResponse($media, $variant, true);
    }

    private function imageResponse(MediaAsset $media, string $variant, bool $public)
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($media->path($variant)), 404);

        return response()->file($disk->path($media->path($variant)), [
            'Content-Type' => 'image/webp', 'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $public ? 'public, max-age=300' : 'private, no-store',
        ]);
    }

    public function original(MediaAsset $media)
    {
        Gate::authorize('download', $media);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($media->path('original')), 404);

        return response()->download($disk->path($media->path('original')), 'original-'.$media->id.'.'.$media->extension, [
            'Content-Type' => 'application/octet-stream', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, MediaAsset $media, MediaLibrary $library)
    {
        $library->delete($media, $request->user());

        return to_route('admin.media.index')->with('status', 'Fotografía eliminada de la biblioteca.');
    }
}

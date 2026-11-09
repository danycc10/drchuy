<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\MediaAsset;
use App\Models\User;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MediaLibrary
{
    public function upload(UploadedFile $file, string $title, User $user): MediaAsset
    {
        Gate::forUser($user)->authorize('create', MediaAsset::class);
        $info = @getimagesize($file->getPathname());
        $mime = $file->getMimeType();
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (! $info || ! isset($extensions[$mime]) || $info['mime'] !== $mime || $file->getSize() > config('media.max_upload_kb') * 1024) {
            $this->invalid('Selecciona una imagen JPG, PNG o WebP válida de hasta 8 MB.');
        }
        [$width, $height] = $info;
        if ($width < 1 || $height < 1) {
            $this->invalid('La fotografía no tiene dimensiones válidas.');
        }
        if ($width * $height > config('media.max_pixels') || max($width, $height) > config('media.max_dimension')) {
            $this->invalid('La imagen excede 12 megapíxeles u 8,000 píxeles por lado. Reduce sus dimensiones antes de subirla.');
        }
        $limit = ini_parse_quantity(ini_get('memory_limit'));
        if ($limit > 0 && memory_get_usage(true) + $width * $height * 12 + 32 * 1024 * 1024 > $limit) {
            $this->invalid('Esta imagen necesita más memoria de la disponible. Reduce sus dimensiones e inténtalo de nuevo.');
        }
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file->getPathname()),
            'image/png' => @imagecreatefrompng($file->getPathname()),
            'image/webp' => @imagecreatefromwebp($file->getPathname()),
        };
        if (! $image) {
            $this->invalid('No pudimos leer la imagen. Exporta una copia JPG, PNG o WebP e inténtalo de nuevo.');
        }
        if ($mime === 'image/jpeg') {
            $orientation = (int) (@exif_read_data($file->getPathname())['Orientation'] ?? 1);
            $image = $this->orient($image, $orientation);
        }
        $directory = 'media/'.Str::uuid();
        $disk = Storage::disk('local');
        try {
            $extension = $extensions[$mime];
            if (! $disk->putFileAs($directory, $file, 'original.'.$extension, ['visibility' => 'private'])) {
                throw new RuntimeException('No se pudo conservar el original.');
            }
            [$display, $displayWidth, $displayHeight] = $this->webp($image, config('media.display_edge'));
            [$thumb] = $this->webp($image, config('media.thumb_edge'));
            if (! $disk->put($directory.'/display.webp', $display) || ! $disk->put($directory.'/thumb.webp', $thumb)) {
                throw new RuntimeException('No se pudieron guardar las imágenes optimizadas.');
            }

            return DB::transaction(function () use ($file, $title, $user, $mime, $extension, $directory, $image, $display, $displayWidth, $displayHeight) {
                $asset = MediaAsset::create([
                    'title' => $title, 'original_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 250),
                    'original_mime' => $mime, 'directory' => $directory, 'extension' => $extension,
                    'width' => imagesx($image), 'height' => imagesy($image),
                    'display_width' => $displayWidth, 'display_height' => $displayHeight,
                    'original_bytes' => $file->getSize(), 'display_bytes' => strlen($display), 'created_by' => $user->id,
                ]);
                AuditEvent::create(['user_id' => $user->id, 'action' => 'media.uploaded', 'metadata' => ['media_id' => $asset->id, 'media_title' => $title]]);

                return $asset;
            });
        } catch (Throwable $exception) {
            $disk->deleteDirectory($directory);
            throw $exception;
        } finally {
            imagedestroy($image);
        }
    }

    private function orient(GdImage $image, int $orientation): GdImage
    {
        if (in_array($orientation, [2, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        } elseif ($orientation === 4) {
            imageflip($image, IMG_FLIP_VERTICAL);
        }
        $angle = match ($orientation) { 3 => 180, 5, 8 => 90, 6, 7 => -90, default => 0 };
        if ($angle) {
            $rotated = imagerotate($image, $angle, 0);
            imagedestroy($image);
            return $rotated;
        }

        return $image;
    }

    private function webp(GdImage $source, int $edge): array
    {
        $ratio = min(1, $edge / max(imagesx($source), imagesy($source)));
        $width = max(1, (int) round(imagesx($source) * $ratio));
        $height = max(1, (int) round(imagesy($source) * $ratio));
        $target = imagecreatetruecolor($width, $height);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
        ob_start();
        try {
            if (! imagewebp($target, null, config('media.quality'))) {
                throw new RuntimeException('No se pudo optimizar la imagen.');
            }
            $bytes = ob_get_contents();
            if ($bytes === '') {
                throw new RuntimeException('La imagen optimizada está vacía.');
            }
            return [$bytes, $width, $height];
        } finally {
            ob_end_clean();
            imagedestroy($target);
        }
    }

    public function delete(MediaAsset $media, User $user): void
    {
        Gate::forUser($user)->authorize('delete', $media);
        DB::transaction(function () use ($media, $user) {
            $locked = MediaAsset::whereKey($media->id)->lockForUpdate()->firstOrFail();
            if ($locked->revisions()->exists()) {
                throw ValidationException::withMessages(['media' => 'Esta imagen se utiliza en un borrador, publicación o historial. Se conserva para poder recuperar esas versiones.']);
            }
            $locked->delete();
            AuditEvent::create(['user_id' => $user->id, 'action' => 'media.deleted', 'metadata' => ['media_id' => $media->id, 'media_title' => $media->title]]);
        });
        if (! Storage::disk('local')->deleteDirectory($media->directory)) {
            Log::warning('Media files pending cleanup', ['directory' => $media->directory]);
        }
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['image' => $message]);
    }
}

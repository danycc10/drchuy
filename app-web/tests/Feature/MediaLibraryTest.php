<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ContentBlock;
use App\Models\MediaAsset;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    private function upload(): MediaAsset
    {
        $this->post(route('admin.media.store'), ['title' => 'Consultorio luminoso', 'image' => UploadedFile::fake()->image('consultorio.jpg', 2000, 1000)])
            ->assertRedirect(route('admin.media.index'))->assertSessionHasNoErrors();

        return MediaAsset::latest('id')->firstOrFail();
    }

    public function test_upload_preserves_original_and_creates_private_optimized_derivatives(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Editor]));
        $media = $this->upload();
        Storage::disk('local')->assertExists([$media->path('original'), $media->path(), $media->path('thumb')]);
        $this->assertSame('image/webp', getimagesize(Storage::disk('local')->path($media->path()))['mime']);
        $this->assertSame([1600, 800], [$media->display_width, $media->display_height]);
        $this->get($media->url(true))->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->get($media->url())->assertNotFound();
        $this->get(route('admin.media.index'))->assertOk()->assertSee('Consultorio luminoso')->assertSee('Sin usar');
        $this->assertDatabaseHas('audit_events', ['action' => 'media.uploaded']);
        $this->get(route('admin.media.original', $media))->assertForbidden();
        auth()->logout();
        $this->get($media->url(true))->assertRedirect(route('login'));
        $this->get($media->url())->assertNotFound();
    }

    public function test_svg_disguised_files_and_excessive_dimensions_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([
            UploadedFile::fake()->createWithContent('photo.jpg', '<?php echo "unsafe";'),
            UploadedFile::fake()->createWithContent('photo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->create('photo.jpg', 8193, 'image/jpeg'),
        ] as $file) {
            $this->post(route('admin.media.store'), ['title' => 'Foto', 'image' => $file])->assertSessionHasErrors('image');
        }
        config(['media.max_pixels' => 100]);
        $this->post(route('admin.media.store'), ['title' => 'Foto', 'image' => UploadedFile::fake()->image('large.png', 20, 20)])->assertSessionHasErrors('image');
        $this->assertDatabaseCount('media_assets', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_jpeg_orientation_is_applied_and_the_original_bytes_are_preserved(): void
    {
        $this->actingAs(User::factory()->create());
        $fixture = UploadedFile::fake()->image('portrait.jpg', 30, 10);
        $jpeg = file_get_contents($fixture->getPathname());
        $exif = "Exif\0\0II".pack('vVv', 42, 8, 1).pack('vvVv', 0x112, 3, 1, 6)."\0\0".pack('V', 0);
        $original = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
        $this->post(route('admin.media.store'), ['title' => 'Retrato', 'image' => UploadedFile::fake()->createWithContent('portrait.jpg', $original)])->assertSessionHasNoErrors();
        $media = MediaAsset::firstOrFail();
        $this->assertSame([10, 30], [$media->display_width, $media->display_height]);
        $this->assertSame($original, Storage::disk('local')->get($media->path('original')));
        $this->assertStringNotContainsString('Exif', Storage::disk('local')->get($media->path()));
    }

    public function test_only_managers_can_delete_unused_assets_and_history_prevents_deletion(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Editor]));
        $media = $this->upload();
        $this->delete(route('admin.media.destroy', $media))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => Role::Owner]));
        $this->get(route('admin.media.original', $media))->assertOk()->assertDownload('original-'.$media->id.'.jpg');
        $this->seed(ContentSeeder::class);
        $revision = ContentBlock::where('key', 'home')->firstOrFail()->currentRevision;
        $revision->mediaAssets()->attach($media);
        $this->delete(route('admin.media.destroy', $media))->assertSessionHasErrors('media');
        $this->assertDatabaseHas('media_assets', ['id' => $media->id]);
        $unused = $this->upload();
        $this->delete(route('admin.media.destroy', $unused))->assertRedirect(route('admin.media.index'));
        $this->assertDatabaseMissing('media_assets', ['id' => $unused->id]);
        Storage::disk('local')->assertMissing($unused->path('original'));
        Storage::disk('local')->assertExists($media->path('original'));
    }
}

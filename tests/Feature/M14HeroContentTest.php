<?php

namespace Tests\Feature;

use App\Jobs\SendContactMessage;
use App\Models\Banner;
use App\Models\User;
use App\Services\BannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class M14HeroContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_a_banner_draft_and_publish_it_separately(): void
    {
        $this->withoutVite();
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);

        $this->actingAs($admin)->post(route('adminpanel.banners.store'), [
            'slot' => 'hero', 'title' => 'Kategóriák egy helyen', 'alt_text' => 'Villamossági termékek kategóriái',
            'link_label' => 'Kategóriák megnyitása', 'link_url' => '/shop',
            'desktop' => UploadedFile::fake()->image('desktop.jpg', 1600, 500),
            'tablet' => UploadedFile::fake()->image('tablet.jpg', 1200, 675),
            'mobile' => UploadedFile::fake()->image('mobile.jpg', 900, 1125),
            'publish' => '0',
        ])->assertRedirect();

        $banner = Banner::firstOrFail();
        $this->assertFalse($banner->is_published);
        $this->assertNotNull($banner->desktop_path);

        $this->actingAs($admin)->post(route('adminpanel.banners.publish', $banner))->assertRedirect();
        $this->assertTrue($banner->refresh()->is_published);
        $this->assertSame($banner->id, app(BannerService::class)->active('hero')->id);
    }

    public function test_banner_publish_rejects_missing_device_variant(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);
        $banner = Banner::create(['slot' => 'hero', 'alt_text' => 'Teszt', 'desktop_path' => 'banners/a.webp', 'tablet_path' => 'banners/b.webp']);

        $this->actingAs($admin)->post(route('adminpanel.banners.publish', $banner))
            ->assertSessionHasErrors('publish');
        $this->assertFalse($banner->refresh()->is_published);
    }

    public function test_content_routes_have_v6_layout_and_catalog_has_honest_empty_state(): void
    {
        $this->withoutVite();
        foreach (['contact', 'impresszum', 'aszf', 'gyik', 'adatkezelesi-nyilatkozat', 'elallasi-nyilatkozat'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('Electro Business');
        }

        $this->get(route('info', ['slug' => 'szallitas']))->assertOk()->assertSee('Szállítás és fizetés');
        $this->get(route('info', ['slug' => 'katalogus']))->assertOk()->assertSee('katalógus jelenleg nincs feltöltve');
    }

    public function test_contact_form_is_validated_and_dispatched_to_the_queue(): void
    {
        $this->withoutVite();
        Bus::fake();

        $this->post(route('sendContact'), [
            'name' => 'Minta János', 'email' => 'janos@example.test', 'subject' => 'Segítség',
            'message' => 'Kérek segítséget egy termékhez.', 'website' => '',
        ])->assertRedirect()->assertSessionHas('success');

        Bus::assertDispatched(SendContactMessage::class);
    }
}

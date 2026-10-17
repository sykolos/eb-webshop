<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_storage_media_is_served_when_the_web_server_symlink_is_unavailable(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/test-media.txt', 'public media');

        $this->get('/storage/products/test-media.txt')
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=86400, public')
            ->assertStreamedContent('public media');
    }

    public function test_public_storage_does_not_allow_path_traversal(): void
    {
        $this->get('/storage/../private/secret.txt')->assertNotFound();
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class M21HardeningTest extends TestCase
{
    public function test_security_headers_are_present_on_public_responses(): void
    {
        $response = $this->get(route('health.live'));

        $response->assertOk()
            ->assertJson(['status' => 'ok'])
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_readiness_endpoint_reports_database_and_storage(): void
    {
        $this->get(route('health.ready'))
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database', true)
            ->assertJsonPath('checks.storage', true);
    }

    public function test_public_readiness_does_not_expose_environment_details(): void
    {
        $response = $this->get(route('health.ready'));

        $this->assertStringNotContainsString(config('database.connections.'.config('database.default').'.database'), $response->getContent());
        $response->assertJsonMissingPath('checks.password');
    }
}

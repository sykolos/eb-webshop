<?php

namespace Tests\Feature;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StagingProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'staging.enabled' => true,
            'staging.username' => 'tester',
            'staging.password_hash' => password_hash('test-only-password', PASSWORD_BCRYPT),
            'staging.revision' => str_repeat('a', 40),
        ]);
        Route::get('/__staging-test', fn () => response('protected'));
    }

    public function test_staging_rejects_missing_and_wrong_credentials(): void
    {
        $this->get('/__staging-test')->assertStatus(401)->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->withBasicAuth('tester', 'wrong')->get('/__staging-test')->assertStatus(401);
    }

    public function test_staging_accepts_valid_credentials_and_identifies_the_release(): void
    {
        $this->withBasicAuth('tester', 'test-only-password')->get('/__staging-test')
            ->assertOk()->assertSee('protected')->assertHeader('X-Release-Sha', str_repeat('a', 40));
    }

    public function test_unconfigured_staging_fails_closed(): void
    {
        config(['staging.password_hash' => '']);
        $this->get('/__staging-test')->assertStatus(503);
    }

    public function test_normal_environments_are_unchanged(): void
    {
        config(['staging.enabled' => false]);
        $this->get('/__staging-test')->assertOk()->assertHeaderMissing('X-Release-Sha')
            ->assertHeaderMissing('WWW-Authenticate');
    }

    public function test_mail_recipients_including_cc_and_bcc_are_replaced(): void
    {
        config(['staging.mail_to' => 'sink@example.test']);
        $messages = [];
        Event::listen(MessageSent::class, function ($event) use (&$messages) {
            $messages[] = $event->message;
        });
        Mail::mailer('array')->raw('Test', function ($message) {
            $message->to('customer@example.test')->cc('copy@example.test')->bcc('hidden@example.test')->subject('Test');
        });
        $this->assertCount(1, $messages);
        $this->assertSame('sink@example.test', $messages[0]->getTo()[0]->getAddress());
        $this->assertCount(1, $messages[0]->getTo());
        $this->assertSame([], $messages[0]->getCc());
        $this->assertSame([], $messages[0]->getBcc());
    }

    public function test_mail_without_a_valid_sink_is_not_sent(): void
    {
        config(['staging.mail_to' => '']);
        $sent = 0;
        Event::listen(MessageSent::class, function () use (&$sent) { $sent++; });
        Mail::mailer('array')->raw('Test', fn ($message) => $message->to('customer@example.test')->subject('Test'));
        $this->assertSame(0, $sent);
    }

    public function test_normal_environment_mail_keeps_original_recipients(): void
    {
        config(['staging.enabled' => false, 'staging.mail_to' => 'sink@example.test']);
        $messages = [];
        Event::listen(MessageSent::class, function ($event) use (&$messages) {
            $messages[] = $event->message;
        });
        Mail::mailer('array')->raw('Test', fn ($message) => $message->to('customer@example.test')->subject('Test'));
        $this->assertCount(1, $messages);
        $this->assertSame('customer@example.test', $messages[0]->getTo()[0]->getAddress());
    }

    public function test_errors_remain_non_indexable_after_staging_authentication(): void
    {
        Route::get('/__staging-not-found', fn () => abort(404));
        $this->withBasicAuth('tester', 'test-only-password')->get('/__staging-not-found')
            ->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
}

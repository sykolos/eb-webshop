<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotifications;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderNotificationService;
use App\Services\SecureFileStorage;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class M04SecurityAndBackgroundTest extends TestCase
{
    use RefreshDatabase;

    public function test_totp_admin_challenge_gates_and_then_opens_admin(): void
    {
        $this->withoutVite();
        $totp = app(TotpService::class);
        $secret = $totp->generateSecret();
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $totp->recoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->get(route('adminpanel'))
            ->assertRedirect(route('admin.2fa.challenge'));

        $code = $totp->code($secret, (int) floor(time() / config('two_factor.period')));
        $this->post(route('admin.2fa.verify'), ['code' => $code])
            ->assertRedirect(route('adminpanel'));

        $this->get(route('adminpanel'))->assertOk();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin.2fa.verified',
            'user_id' => $admin->id,
        ]);
    }

    public function test_login_updates_last_login_and_writes_audit_event(): void
    {
        $user = User::factory()->create(['password' => bcrypt('valid-password')]);

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'valid-password',
        ])->assertRedirect('/');

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'user_id' => $user->id,
        ]);
    }

    public function test_new_order_notifications_are_dispatched_as_a_job(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'address' => 'Teszt utca 1.',
            'total' => 1000,
            'status' => 'függőben',
        ]);

        app(OrderNotificationService::class)->dispatch($order);

        Queue::assertPushed(SendOrderNotifications::class, fn (SendOrderNotifications $job) => $job->orderId === $order->id);
    }

    public function test_scheduler_heartbeat_is_environment_specific(): void
    {
        $this->artisan('system:heartbeat')->assertExitCode(0);

        $this->assertDatabaseHas('scheduler_heartbeats', [
            'name' => 'laravel-scheduler',
            'environment' => 'testing',
        ]);
    }

    public function test_private_file_storage_uses_generated_names_and_private_disk(): void
    {
        Storage::fake('private');

        $path = app(SecureFileStorage::class)->storePrivate(
            UploadedFile::fake()->create('banner.pdf', 100, 'application/pdf'),
            'admin-guides',
            ['pdf'],
        );

        $this->assertStringStartsWith('admin-guides/', $path);
        $this->assertStringEndsWith('.pdf', $path);
        $this->assertNotSame('admin-guides/banner.pdf', $path);
        Storage::disk('private')->assertExists($path);

        $user = User::factory()->create();
        $url = app(SecureFileStorage::class)->temporaryDownloadUrl($path, 1);
        $this->actingAs($user)->get($url)->assertOk();
        $this->actingAs($user)->get($url.'&expires=1')->assertForbidden();
    }
}

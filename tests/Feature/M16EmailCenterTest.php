<?php

namespace Tests\Feature;

use App\Jobs\SendCampaignEmail;
use App\Models\EmailCampaign;
use App\Models\EmailDelivery;
use App\Models\EmailSuppression;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\EmailCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class M16EmailCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_email_center_and_non_admin_cannot(): void
    {
        $this->withoutVite();
        $this->get(route('adminpanel.emails'))->assertRedirect(route('login'));
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $this->actingAs($admin)->get(route('adminpanel.emails'))->assertOk()->assertSee('Emailközpont és hírlevél')->assertSee('Hírlevél címzettek');
    }

    public function test_campaign_audience_requires_explicit_preference_and_excludes_suppressed_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $included = User::factory()->create(['account_status' => 'active']);
        UserPreference::create(['user_id' => $included->id, 'newsletter_subscribed' => true, 'marketing_notifications' => false]);
        $legacy = User::factory()->create(['account_status' => 'active']);
        $suppressed = User::factory()->create(['account_status' => 'active']);
        UserPreference::create(['user_id' => $suppressed->id, 'newsletter_subscribed' => true, 'marketing_notifications' => false]);
        EmailSuppression::create(['email' => $suppressed->email, 'reason' => 'bounce', 'source' => 'provider']);

        $service = app(EmailCampaignService::class);
        $campaign = $service->createCampaign(['name' => 'M16 teszt', 'subject' => 'Teszt', 'html_body' => '<p>Kedves {{name}}</p>', 'text_body' => 'Kedves {{name}}', 'segment' => 'newsletter', 'timezone' => 'Europe/Budapest'], $admin);
        $this->assertSame(1, $campaign->recipient_count);
        $this->assertSame([$included->id], $service->audience()->pluck('id')->all());
        $this->assertNotContains($legacy->id, $service->audience()->pluck('id')->all());
    }

    public function test_campaign_queue_is_idempotent_and_mail_delivery_is_recorded(): void
    {
        Queue::fake();
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $user = User::factory()->create(['account_status' => 'active']);
        UserPreference::create(['user_id' => $user->id, 'newsletter_subscribed' => true, 'marketing_notifications' => false]);
        $campaign = EmailCampaign::create(['name' => 'Queue teszt', 'subject' => 'Tárgy', 'html_body' => '<p>{{name}}</p>', 'text_body' => 'Szia {{name}}', 'type' => 'marketing', 'status' => 'draft', 'timezone' => 'Europe/Budapest', 'segment' => 'newsletter', 'created_by_admin_id' => $admin->id]);

        $service = app(EmailCampaignService::class);
        $this->assertSame(1, $service->queueCampaign($campaign));
        $this->assertSame(0, $service->queueCampaign($campaign->fresh()));
        $delivery = EmailDelivery::firstOrFail();
        Queue::assertPushed(SendCampaignEmail::class, 1);
        (new SendCampaignEmail($delivery->id))->handle();
        Mail::assertSent(\App\Mail\CampaignMail::class, 1);
        $this->assertDatabaseHas('email_deliveries', ['id' => $delivery->id, 'status' => 'sent']);
    }

    public function test_signed_unsubscribe_creates_suppression_and_disables_preferences(): void
    {
        $user = User::factory()->create(['account_status' => 'active']);
        UserPreference::create(['user_id' => $user->id, 'newsletter_subscribed' => true, 'marketing_notifications' => true]);
        $campaign = EmailCampaign::create(['name' => 'Leiratkozás', 'subject' => 'Tárgy', 'html_body' => '<p>Test</p>', 'type' => 'marketing', 'status' => 'processing', 'timezone' => 'Europe/Budapest', 'segment' => 'newsletter']);
        $delivery = EmailDelivery::create(['campaign_id' => $campaign->id, 'user_id' => $user->id, 'email' => $user->email, 'type' => 'marketing', 'status' => 'queued', 'idempotency_key' => 'unsubscribe:test']);
        $this->get(URL::signedRoute('email.unsubscribe', ['delivery' => $delivery]))->assertOk()->assertSee('Leiratkozás sikeres');
        $this->assertDatabaseHas('email_suppressions', ['email' => $user->email, 'reason' => 'unsubscribe']);
        $this->assertDatabaseHas('user_preferences', ['user_id' => $user->id, 'newsletter_subscribed' => 0, 'marketing_notifications' => 0]);
    }
}

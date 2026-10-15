<?php

namespace Tests\Feature;

use App\Jobs\SendAutomationNotification;
use App\Models\AutomationRule;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\TicketStatusHistory;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class M18TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_can_create_and_read_own_ticket_but_internal_content_is_hidden(): void
    {
        Storage::fake('private');
        $partner = User::factory()->create(['account_status' => 'active']);
        $other = User::factory()->create(['account_status' => 'active']);
        $order = $this->order($partner);

        $response = $this->actingAs($partner)->post(route('account.tickets.store'), [
            'subject' => 'M18 teszt kérdés', 'body' => 'Szeretnék segítséget kérni.', 'type' => 'order', 'priority' => 'high', 'order_id' => $order->id,
            'attachment' => UploadedFile::fake()->create('megrendeles.pdf', 10, 'application/pdf'),
        ]);
        $ticket = Ticket::firstOrFail();
        $response->assertRedirect(route('account.tickets.show', $ticket));
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'user_id' => $partner->id, 'order_id' => $order->id, 'priority' => 'high']);
        $this->assertDatabaseHas('ticket_attachments', ['original_name' => 'megrendeles.pdf']);

        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        app(TicketService::class)->addMessage($ticket, $admin, 'Belső fejlesztői megjegyzés.', true);
        $this->actingAs($partner)->get(route('account.tickets.show', $ticket))->assertOk()->assertSee('Szeretnék segítséget kérni.')->assertDontSee('Belső fejlesztői megjegyzés.');
        $this->actingAs($other)->get(route('account.tickets.show', $ticket))->assertNotFound();
    }

    public function test_admin_can_filter_ticket_list_assign_change_status_and_send_public_reply(): void
    {
        Queue::fake();
        $partner = User::factory()->create(['account_status' => 'active']);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $ticket = app(TicketService::class)->create($partner, ['subject' => 'Állapot teszt', 'body' => 'Első üzenet', 'type' => 'technical', 'priority' => 'normal']);
        AutomationRule::where('key', 'ticket_update')->update(['enabled' => true]);

        $this->actingAs($admin)->get(route('adminpanel.tickets'))->assertOk()->assertSee($ticket->ticket_number)->assertSee('Állapot teszt');
        $this->actingAs($admin)->put(route('adminpanel.tickets.update', $ticket), ['status' => 'in_progress', 'assigned_to' => $admin->id, 'note' => 'Átvéve'])->assertRedirect();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'in_progress', 'assigned_to' => $admin->id]);
        $this->assertDatabaseHas('ticket_status_histories', ['ticket_id' => $ticket->id, 'to_status' => 'in_progress']);
        $this->actingAs($admin)->post(route('adminpanel.tickets.messages.store', $ticket), ['body' => 'Ügyfélnek küldött válasz'])->assertRedirect();
        $this->assertDatabaseHas('ticket_messages', ['ticket_id' => $ticket->id, 'body' => 'Ügyfélnek küldött válasz', 'is_internal' => 0]);
        Queue::assertPushed(SendAutomationNotification::class, 1);
    }

    public function test_internal_message_and_attachment_are_admin_only_and_download_is_scoped(): void
    {
        Storage::fake('private');
        $partner = User::factory()->create(['account_status' => 'active']);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $ticket = app(TicketService::class)->create($partner, ['subject' => 'Biztonság teszt', 'body' => 'Nyitó üzenet', 'type' => 'general', 'priority' => 'normal']);
        $this->actingAs($admin)->post(route('adminpanel.tickets.messages.store', $ticket), ['body' => 'Belső csatolmány', 'is_internal' => 1, 'attachment' => UploadedFile::fake()->create('belső.pdf', 12, 'application/pdf')])->assertRedirect();
        $attachment = TicketAttachment::latest()->firstOrFail();
        $this->actingAs($partner)->get(route('account.tickets.attachments.download', $attachment))->assertNotFound();
        $this->actingAs($admin)->get(route('adminpanel.tickets.attachments.download', $attachment))->assertOk();
        $this->assertDatabaseHas('ticket_messages', ['ticket_id' => $ticket->id, 'is_internal' => 1]);
        $this->assertSame(2, TicketMessage::where('ticket_id', $ticket->id)->count());
    }

    public function test_status_transitions_are_limited_and_exports_are_registered(): void
    {
        $partner = User::factory()->create(['account_status' => 'active']);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $ticket = app(TicketService::class)->create($partner, ['subject' => 'Átmenet teszt', 'body' => 'Nyitás', 'type' => 'general', 'priority' => 'normal']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(TicketService::class)->changeStatus($ticket, $admin, 'resolved');
    }

    public function test_ticket_cleanup_removes_only_expired_closed_tickets_and_private_files(): void
    {
        Storage::fake('private');
        $partner = User::factory()->create(['account_status' => 'active']);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $expired = app(TicketService::class)->create($partner, ['subject' => 'Lejárt ticket', 'body' => 'Régi ügy', 'type' => 'general', 'priority' => 'normal']);
        app(TicketService::class)->addMessage($expired, $admin, 'Régi belső fájl', true, UploadedFile::fake()->create('regi.pdf', 12, 'application/pdf'));
        $attachment = TicketAttachment::latest()->firstOrFail();
        $expired->forceFill(['status' => 'closed', 'closed_at' => now()->subMonths(25)])->save();
        $fresh = app(TicketService::class)->create($partner, ['subject' => 'Friss ticket', 'body' => 'Új ügy', 'type' => 'general', 'priority' => 'normal']);

        $this->assertSame(1, app(TicketService::class)->purgeExpired());
        $this->assertDatabaseMissing('tickets', ['id' => $expired->id]);
        $this->assertDatabaseHas('tickets', ['id' => $fresh->id]);
        Storage::disk('private')->assertMissing($attachment->path);
    }

    private function order(User $user): Order
    {
        return Order::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'address' => 'Teszt utca 1.', 'city' => 'Budapest', 'total' => 1000, 'status' => 'pending']);
    }
}

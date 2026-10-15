<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M12UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_list_supports_company_role_and_order_activity_filters(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);
        $partner = User::factory()->create(['name' => 'Aktív Partner', 'account_status' => 'active']);
        User_invoce::create(['user_id' => $partner->id, 'company_name' => 'M12 Teszt Kft.', 'address' => 'Fő út 1.']);
        Order::create(['user_id' => $partner->id, 'name' => 'M12 Teszt Kft.', 'email' => $partner->email, 'address' => 'Fő út 1.', 'total' => 1200, 'status' => 'függőben']);

        $this->actingAs($admin)->get(route('adminpanel.users', [
            'search' => 'M12 Teszt', 'role' => 'partner', 'status' => 'active',
        ]))->assertOk()->assertSee('M12 Teszt Kft.')->assertSee('Aktív Partner');

        $this->actingAs($admin)->get(route('adminpanel.users', ['without_orders' => 1]))
            ->assertOk()->assertDontSee('Aktív Partner');
    }

    public function test_admin_can_update_partner_profile_invoice_and_address_with_audit(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);
        $partner = User::factory()->create(['account_status' => 'pending']);
        User_invoce::create(['user_id' => $partner->id, 'company_name' => 'Régi Kft.', 'address' => 'Régi út']);
        $shipping = User_shipping::create(['user_id' => $partner->id, 'address' => 'Régi szállítás', 'is_default' => true]);

        $this->actingAs($admin)->put(route('adminpanel.users.user_update', $partner->id), [
            'name' => 'Módosított Partner', 'email' => $partner->email,
            'role' => 'partner', 'account_status' => 'active', 'admin_note' => 'Ellenőrizve',
            'invoice' => ['company_name' => 'Új Kft.', 'address' => 'Új számlázási út', 'country' => 'Magyarország'],
            'shipping' => [$shipping->id => ['address' => 'Új szállítási út', 'is_default' => 1]],
        ])->assertRedirect(route('adminpanel.users.user_view', $partner->id));

        $this->assertSame('Új Kft.', $partner->fresh()->user_invoice->company_name);
        $this->assertSame('Új szállítási út', $shipping->fresh()->address);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.user.updated', 'auditable_id' => $partner->id]);
    }

    public function test_last_admin_cannot_be_demoted_and_partner_archive_preserves_orders(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);
        User_invoce::create(['user_id' => $admin->id, 'company_name' => 'Admin Kft.', 'address' => 'Admin út']);

        $this->actingAs($admin)->put(route('adminpanel.users.user_update', $admin->id), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'partner', 'account_status' => 'active',
            'invoice' => ['company_name' => 'Admin Kft.'],
        ])->assertSessionHasErrors('role');

        $partner = User::factory()->create(['account_status' => 'active']);
        $order = Order::create(['user_id' => $partner->id, 'name' => 'Archiválandó', 'email' => $partner->email, 'address' => 'Út', 'total' => 900, 'status' => 'függőben']);
        $this->actingAs($admin)->delete(route('adminpanel.users.user_destroy', $partner->id))->assertRedirect(route('adminpanel.users'));

        $this->assertSoftDeleted('users', ['id' => $partner->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $partner->id]);
    }
}

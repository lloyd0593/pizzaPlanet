<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── Login Page ─────────────────────────────────────────────────

    public function test_admin_login_page_loads(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.auth.login');
    }

    public function test_authenticated_admin_is_redirected_from_login(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->get(route('admin.login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    // ─── Login ──────────────────────────────────────────────────────

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@pizzaplanet.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@pizzaplanet.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_user_cannot_login_to_admin(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@pizzaplanet.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@pizzaplanet.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->post(route('admin.login.submit'), []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_validates_email_format(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ─── Logout ─────────────────────────────────────────────────────

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    // ─── Admin Middleware ────────────────────────────────────────────

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_admin_pizzas(): void
    {
        $response = $this->get(route('admin.pizzas'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_access_admin_toppings(): void
    {
        $response = $this->get(route('admin.toppings'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_access_admin_orders(): void
    {
        $response = $this->get(route('admin.orders'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_all_admin_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.dashboard'))->assertStatus(200);
        $this->get(route('admin.pizzas'))->assertStatus(200);
        $this->get(route('admin.toppings'))->assertStatus(200);
        $this->get(route('admin.orders'))->assertStatus(200);
    }
}

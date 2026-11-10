<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => $role.'@example.test', 'role' => $role,
            'is_active' => true, 'password' => 'password',
        ]);
    }

    public function test_landing_page_and_availability_search_render(): void
    {
        $this->seed(RoomSeeder::class);

        $this->get('/')->assertOk()->assertSee('Cek Ketersediaan')->assertSee('Standard Room AC');

        $in = today()->addDays(5)->toDateString();
        $out = today()->addDays(7)->toDateString();
        $this->get("/?check_in={$in}&check_out={$out}&guests=2")->assertOk()->assertSee('unit tersedia');
    }

    public function test_chatbot_basic_mode_answers_and_can_point_to_whatsapp(): void
    {
        $this->seed(RoomSeeder::class);

        $this->postJson('/chatbot', ['message' => 'Berapa harga kamar?'])
            ->assertOk()->assertJsonStructure(['reply', 'actions', 'mode'])->assertJsonPath('mode', 'basic');

        $res = $this->postJson('/chatbot', ['message' => 'Saya mau pesan kamar'])->assertOk();
        $this->assertStringContainsString('wa.me', $res->json('actions.0.url'));
    }

    public function test_guests_cannot_open_staff_pages(): void
    {
        $this->get('/pengelola/dashboard')->assertRedirect('/login');
        $this->get('/owner/dashboard')->assertRedirect('/login');
        $this->get('/reservasi')->assertRedirect('/login');
    }

    public function test_roles_are_enforced(): void
    {
        $this->seed(RoomSeeder::class);

        $this->actingAs($this->user('owner'))
            ->get('/reservasi/buat')->assertForbidden();
        $this->actingAs($this->user('pengelola'))
            ->get('/owner/dashboard')->assertForbidden();
    }

    public function test_dashboards_and_calendar_render_for_each_role(): void
    {
        $this->seed(RoomSeeder::class);

        $this->actingAs($this->user('pengelola'))->get('/pengelola/dashboard')->assertOk();
        $this->get('/kalender')->assertOk();
        $this->get('/reservasi/buat')->assertOk();
        $this->get('/kamar')->assertOk();

        $this->actingAs($this->user('owner'))->get('/owner/dashboard')->assertOk();
        $this->get('/kalender')->assertOk();
        $this->get('/owner/pembayaran')->assertOk();
        $this->get('/owner/audit')->assertOk();
    }

    public function test_login_works_and_redirects_by_role(): void
    {
        $this->user('owner');

        $this->post('/login', ['email' => 'owner@example.test', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->post('/logout');
        $this->post('/login', ['email' => 'owner@example.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');
    }
}

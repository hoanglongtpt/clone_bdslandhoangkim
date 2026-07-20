<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CrmAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Hoàng Kim Land CRM');
    }

    public function test_admin_can_see_dashboard_and_user_management(): void
    {
        $admin = User::factory()->create(['password' => 'password', 'role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->get('/')->assertOk()
            ->assertSee('Bộ lọc')
            ->assertSee('crm-pagination', false)
            ->assertSee('aria-label="Trang cuối"', false)
            ->assertSee('data-view-notes', false)
            ->assertSee('data-add-note', false);
        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee('phân quyền');
        $this->actingAs($admin)->get(route('profile.show'))->assertOk()->assertSee('Thông tin cá nhân');
        $this->actingAs($admin)->get(route('notes.history'))->assertOk()->assertSee('Ghi chú của tôi');
        $this->actingAs($admin)->get(route('profile.avatar'))->assertNotFound();
    }

    public function test_viewer_is_limited_to_assigned_project(): void
    {
        $viewer = User::factory()->create(['password' => 'password', 'role' => 'viewer', 'is_active' => true]);
        $viewer->projects()->attach(Project::where('project_name', 'Victoria Village')->value('id'));
        $allowed = Property::where('project_id', 3)->firstOrFail();
        $denied = Property::where('project_id', '<>', 3)->firstOrFail();

        $this->actingAs($viewer)->get('/')->assertOk()->assertSee('Victoria Village')->assertDontSee('THE SUN AVENUE Q2');
        $this->actingAs($viewer)->get(route('properties.show', $allowed))->assertOk();
        $this->actingAs($viewer)->get(route('properties.show', $denied))->assertForbidden();
        $this->actingAs($viewer)->get(route('properties.edit', $allowed))->assertForbidden();
        $this->actingAs($viewer)->get('/')->assertOk()
            ->assertSee('data-dynamic-gallery', false);
        $this->actingAs($viewer)->getJson(route('properties.images', $allowed))->assertOk()->assertJsonStructure(['property', 'images']);
        $this->actingAs($viewer)->getJson(route('properties.images', $denied))->assertForbidden();
        $this->actingAs($viewer)
            ->getJson(route('properties.notes.index', [$allowed, '1']))
            ->assertOk()
            ->assertJsonStructure(['property' => ['id', 'code'], 'group', 'title', 'notes']);
    }

    public function test_direct_property_access_does_not_expose_the_whole_project(): void
    {
        $viewer = User::factory()->create(['password' => 'password', 'role' => 'viewer', 'is_active' => true]);
        $allowed = Property::where('project_id', 3)->firstOrFail();
        $denied = Property::where('project_id', 3)->where('id', '<>', $allowed->id)->firstOrFail();
        $viewer->properties()->attach($allowed->id);

        $this->actingAs($viewer)->get(route('properties.show', $allowed))->assertOk();
        $this->actingAs($viewer)->get(route('properties.show', $denied))->assertForbidden();
    }

    public function test_manager_cannot_move_property_to_unassigned_project(): void
    {
        $manager = User::factory()->create(['password' => 'password', 'role' => 'manager', 'is_active' => true]);
        $manager->projects()->attach(3);
        $property = Property::where('project_id', 3)->firstOrFail();

        $this->actingAs($manager)->put(route('properties.update', $property), [
            'code' => $property->code,
            'project_id' => Project::where('id', '<>', 3)->value('id'),
        ])->assertForbidden();
    }
}

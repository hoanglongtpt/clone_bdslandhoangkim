<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function test_members_can_add_customers_but_only_admin_can_remove_them(): void
    {
        $property = Property::firstOrFail();
        $member = User::factory()->create(['password' => 'password', 'role' => 'viewer', 'is_active' => true]);
        $member->properties()->attach($property->id);

        $this->actingAs($member)->post(route('properties.customers.store', $property), [
            'full_name' => 'Khách hàng thử nghiệm', 'phone1' => '0912 345 678',
        ])->assertRedirect();

        $customer = Customer::query()->where('phone1', '0912345678')->firstOrFail();
        $this->assertDatabaseHas('property_customers', ['property_id' => $property->id, 'customer_id' => $customer->id]);
        $this->actingAs($member)->delete(route('properties.customers.destroy', [$property, $customer]))->assertForbidden();
        $this->assertDatabaseHas('property_customers', ['property_id' => $property->id, 'customer_id' => $customer->id]);

        $admin = User::factory()->create(['password' => 'password', 'role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->delete(route('properties.customers.destroy', [$property, $customer]))->assertRedirect();
        $this->assertDatabaseMissing('property_customers', ['property_id' => $property->id, 'customer_id' => $customer->id]);
    }

    public function test_managers_can_upload_property_images_but_viewers_cannot(): void
    {
        Storage::fake('local');
        $property = Property::firstOrFail();
        $manager = User::factory()->create(['password' => 'password', 'role' => 'manager', 'is_active' => true]);
        $manager->properties()->attach($property->id);

        $this->actingAs($manager)->post(route('properties.images.store', $property), [
            'images' => [UploadedFile::fake()->image('living-room.jpg', 1200, 800)],
        ])->assertRedirect();

        $media = $property->media()->where('download_status', 'uploaded')->firstOrFail();
        Storage::disk('local')->assertExists(Str::after($media->local_path, 'storage:'));
        $this->actingAs($manager)->get(route('media.show', $media))->assertOk();

        $viewer = User::factory()->create(['password' => 'password', 'role' => 'viewer', 'is_active' => true]);
        $viewer->properties()->attach($property->id);
        $this->actingAs($viewer)->post(route('properties.images.store', $property), [
            'images' => [UploadedFile::fake()->image('blocked.jpg')],
        ])->assertForbidden();
    }
}

<?php

namespace Tests\Feature;

use App\Models\FieldStaff;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_field_staff_management(): void
    {
        $admin = $this->userWithRole('ADMIN');

        $this->actingAs($admin)
            ->get(route('field-staff.index'))
            ->assertOk()
            ->assertSee('Quản lý Nhân viên hiện trường');
    }

    public function test_dispatch_can_create_field_staff(): void
    {
        $dispatch = $this->userWithRole('DISPATCH');
        $fieldUser = $this->userWithRole('FIELD', ['email' => 'field.staff@example.test']);
        $location = Location::factory()->create([
            'type' => 'warehouse',
        ]);

        $response = $this->actingAs($dispatch)
            ->post(route('field-staff.store'), [
                'user_id' => $fieldUser->id,
                'full_name' => 'Nguyễn Văn Hiện Trường',
                'phone' => '0909009009',
                'date_of_birth' => '1995-05-21',
                'certificates' => 'Chứng chỉ an toàn kho bãi',
                'responsible_location_id' => $location->id,
                'start_date' => '2026-05-18',
                'status' => 'active',
                'note' => 'Phụ trách kho trung tâm.',
            ]);

        $response->assertRedirect(route('field-staff.index'));

        $this->assertDatabaseHas('field_staff', [
            'user_id' => $fieldUser->id,
            'full_name' => 'Nguyễn Văn Hiện Trường',
            'responsible_location_id' => $location->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('field_staff_location', [
            'location_id' => $location->id,
        ]);
    }

    public function test_non_admin_or_dispatch_cannot_manage_field_staff(): void
    {
        $accountant = $this->userWithRole('ACCOUNTANT');

        $this->actingAs($accountant)
            ->get(route('field-staff.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_database_seeder_creates_linked_field_staff_records(): void
    {
        $this->seed();

        $this->assertDatabaseCount('field_staff', 3);
        $this->assertDatabaseMissing('field_staff', [
            'user_id' => null,
        ]);
    }

    public function test_field_staff_must_use_depot_or_warehouse_location(): void
    {
        $admin = $this->userWithRole('ADMIN');
        $port = Location::factory()->create([
            'type' => 'port',
        ]);

        $this->actingAs($admin)
            ->post(route('field-staff.store'), [
                'full_name' => 'Nhân viên cảng',
                'phone' => '0909009009',
                'date_of_birth' => '1995-05-21',
                'responsible_location_id' => $port->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('responsible_location_ids.0');

        $this->assertDatabaseCount('field_staff', 0);
    }

    public function test_field_staff_can_be_responsible_for_multiple_locations(): void
    {
        $admin = $this->userWithRole('ADMIN');
        $warehouse = Location::factory()->create(['type' => 'warehouse']);
        $depot = Location::factory()->create(['type' => 'depot']);

        $this->actingAs($admin)
            ->post(route('field-staff.store'), [
                'full_name' => 'Nhân viên nhiều khu vực',
                'phone' => '0909009009',
                'date_of_birth' => '21/05/1995',
                'responsible_location_ids' => [$warehouse->id, $depot->id],
                'status' => 'active',
            ])
            ->assertRedirect(route('field-staff.index'));

        $this->assertDatabaseHas('field_staff', [
            'full_name' => 'Nhân viên nhiều khu vực',
            'responsible_location_id' => $warehouse->id,
        ]);
        $this->assertDatabaseHas('field_staff_location', ['location_id' => $warehouse->id]);
        $this->assertDatabaseHas('field_staff_location', ['location_id' => $depot->id]);
    }

    public function test_field_staff_account_dropdown_only_shows_unlinked_field_users(): void
    {
        $admin = $this->userWithRole('ADMIN');
        $fieldRole = Role::factory()->create([
            'role_code' => 'FIELD',
            'role_name' => 'FIELD',
        ]);
        $linkedUser = User::factory()->create([
            'email' => 'linked.field@example.test',
            'role_id' => $fieldRole->id,
        ]);
        $unlinkedUser = User::factory()->create([
            'email' => 'unlinked.field@example.test',
            'role_id' => $fieldRole->id,
        ]);
        $location = Location::factory()->create(['type' => 'warehouse']);

        FieldStaff::factory()->create([
            'user_id' => $linkedUser->id,
            'responsible_location_id' => $location->id,
        ]);

        $this->actingAs($admin)
            ->get(route('field-staff.index'))
            ->assertOk()
            ->assertViewHas('fieldUsers', function ($users) use ($linkedUser, $unlinkedUser): bool {
                return $users->contains('id', $unlinkedUser->id)
                    && ! $users->contains('id', $linkedUser->id);
            });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function userWithRole(string $roleCode, array $attributes = []): User
    {
        $role = Role::factory()->create([
            'role_code' => $roleCode,
            'role_name' => $roleCode,
        ]);

        return User::factory()->create($attributes + [
            'role_id' => $role->id,
        ]);
    }
}

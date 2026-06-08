<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Support\LogisticsOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_driver_account_creates_linked_driver_profile(): void
    {
        $admin = $this->actingAdmin();
        $driverRole = $this->role('DRIVER');

        $response = $this->actingAs($admin)->post(route('users.store'), $this->basePayload($driverRole->id, [
            'email' => 'driver.profile@example.test',
            'driver_phone' => '0912345678',
            'driver_license_number' => 'GPLX-NEW-001',
            'driver_rank' => array_key_first(LogisticsOptions::driverRanks()),
        ]));

        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'driver.profile@example.test')->firstOrFail();

        $this->assertDatabaseHas('drivers', [
            'user_id' => $user->id,
            'full_name' => 'Nguyen Van Tai',
            'phone' => '0912345678',
            'license_number' => 'GPLX-NEW-001',
            'status' => 'active',
        ]);
    }

    public function test_creating_field_account_creates_linked_field_staff_profile(): void
    {
        $admin = $this->actingAdmin();
        $fieldRole = $this->role('FIELD');
        $location = Location::factory()->create(['type' => 'warehouse']);

        $response = $this->actingAs($admin)->post(route('users.store'), $this->basePayload($fieldRole->id, [
            'email' => 'field.profile@example.test',
            'field_phone' => '0987654321',
            'field_responsible_location_ids' => [$location->id],
            'field_certificates' => 'Nghiep vu hien truong',
        ]));

        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'field.profile@example.test')->firstOrFail();

        $this->assertDatabaseHas('field_staff', [
            'user_id' => $user->id,
            'full_name' => 'Nguyen Van Tai',
            'phone' => '0987654321',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('field_staff_location', [
            'location_id' => $location->id,
        ]);
    }

    public function test_profile_validation_prevents_orphan_driver_account(): void
    {
        $admin = $this->actingAdmin();
        $driverRole = $this->role('DRIVER');

        $response = $this->actingAs($admin)->from(route('users.create'))->post(route('users.store'), $this->basePayload($driverRole->id, [
            'email' => 'missing.driver.profile@example.test',
        ]));

        $response->assertRedirect(route('users.create'));
        $response->assertSessionHasErrors(['driver_phone', 'driver_license_number']);

        $this->assertDatabaseMissing('users', [
            'email' => 'missing.driver.profile@example.test',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function basePayload(int $roleId, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nguyen Van Tai',
            'email' => 'employee.profile@example.test',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role_id' => $roleId,
            'position' => array_key_first(LogisticsOptions::positions()),
            'department' => array_key_first(LogisticsOptions::departments()),
            'date_of_birth' => '15/05/1992',
            'joined_at' => '20/05/2026',
        ], $overrides);
    }

    private function actingAdmin(): User
    {
        return User::factory()->create([
            'role_id' => $this->role('ADMIN')->id,
        ]);
    }

    private function role(string $roleCode): Role
    {
        return Role::firstOrCreate(
            ['role_code' => $roleCode],
            ['role_name' => $roleCode, 'description' => $roleCode]
        );
    }
}

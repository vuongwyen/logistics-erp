<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DispatchOrder;
use App\Models\Driver;
use App\Models\FieldAssignment;
use App\Models\FieldStaff;
use App\Models\Location;
use App\Models\Role;
use App\Models\ShippingJob;
use App\Models\User;
use App\Support\LogisticsOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CustomerFeedbackWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_dark_mode_can_be_enabled_and_disabled(): void
    {
        $user = $this->userWithRole('ADMIN', [
            'email' => 'admin@example.test',
            'is_dark_mode' => false,
        ]);

        $this->actingAs($user)
            ->post(route('profile.update'), [
                'name' => 'Admin User',
                'email' => 'admin@example.test',
                'theme_color' => '#1a237e',
                'is_dark_mode' => '1',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'date_format' => 'd/m/Y',
                'two_factor_enabled' => '0',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue($user->fresh()->is_dark_mode);

        $this->actingAs($user->fresh())
            ->post(route('profile.update'), [
                'name' => 'Admin User',
                'email' => 'admin@example.test',
                'theme_color' => '#1a237e',
                'is_dark_mode' => '0',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'date_format' => 'd/m/Y',
                'two_factor_enabled' => '0',
            ])
            ->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->is_dark_mode);
    }

    public function test_customer_tax_code_phone_name_and_text_contact_are_validated(): void
    {
        $sales = $this->userWithRole('SALES');

        $this->actingAs($sales)
            ->post(route('customers.store'), [
                'customer_name' => 'Khách  hàng 123',
                'company_name' => 'Công ty kiểm thử',
                'tax_code' => 'ABC123',
                'address' => 'Hải Phòng',
                'phone' => '123456789',
                'email' => 'customer@example.test',
                'contact_person' => 'Giám đốc',
            ])
            ->assertSessionHasErrors(['customer_name', 'tax_code', 'phone']);

        $this->actingAs($sales)
            ->post(route('customers.store'), [
                'customer_name' => 'khách hàng kiểm thử',
                'company_name' => 'công ty kiểm thử',
                'tax_code' => '1234567890',
                'address' => 'Hải Phòng',
                'phone' => '0901234567',
                'email' => 'customer@example.test',
                'contact_person' => 'Giám đốc',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'customer_name' => 'Khách Hàng Kiểm Thử',
            'contact_person' => 'Giám Đốc',
        ]);
    }

    public function test_accountant_approval_creates_internal_dispatch_document_and_updates_job(): void
    {
        $accountant = $this->userWithRole('ACCOUNTANT');
        $job = ShippingJob::factory()->create(['status' => 'new']);
        $dispatchOrder = DispatchOrder::factory()->create([
            'shipping_job_id' => $job->id,
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $this->actingAs($accountant)
            ->post(route('dispatch-orders.approve', $dispatchOrder))
            ->assertRedirect();

        $this->assertDatabaseHas('dispatch_orders', [
            'id' => $dispatchOrder->id,
            'approval_status' => 'approved',
            'approved_by' => $accountant->id,
        ]);

        $this->assertDatabaseHas('shipping_jobs', [
            'id' => $job->id,
            'status' => 'dispatched',
        ]);

        $this->assertDatabaseHas('documents', [
            'shipping_job_id' => $job->id,
            'doc_category' => 'Lệnh điều xe',
            'file_url' => 'internal://dispatch-order/'.$dispatchOrder->id,
        ]);
    }

    public function test_shipping_job_container_customs_and_vietnamese_date_are_validated(): void
    {
        $sales = $this->userWithRole('SALES');
        $customer = Customer::factory()->create();
        $pickup = Location::factory()->create();
        $delivery = Location::factory()->create();

        $payload = [
            'customer_id' => $customer->id,
            'pickup_location_id' => $pickup->id,
            'delivery_location_id' => $delivery->id,
            'cargo_type' => 'Hàng kiểm thử',
            'container_type' => '20DC',
            'expected_date' => now()->format('d/m/Y'),
        ];

        $this->actingAs($sales)
            ->post(route('shipping-jobs.store'), $payload + [
                'container_number' => 'AB123',
                'customs_declaration_no' => 'ABC',
            ])
            ->assertSessionHasErrors(['container_number', 'customs_declaration_no']);

        $this->actingAs($sales)
            ->post(route('shipping-jobs.store'), $payload + [
                'container_number' => 'tcnu1234567',
                'customs_declaration_no' => '123456789012',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('shipping_jobs', [
            'container_number' => 'TCNU1234567',
            'customs_declaration_no' => '123456789012',
            'expected_date' => now()->toDateString(),
        ]);
    }

    public function test_only_admin_or_dispatch_can_update_actual_fuel_liters(): void
    {
        $driverUser = $this->userWithRole('DRIVER');
        $driver = Driver::factory()->create(['user_id' => $driverUser->id]);
        $dispatchOrder = DispatchOrder::factory()->create([
            'driver_id' => $driver->id,
            'dispatch_status' => 'dispatched',
            'approval_status' => 'approved',
            'actual_fuel_liters' => null,
        ]);

        $this->actingAs($driverUser)
            ->patch(route('dispatch-orders.update-status', $dispatchOrder), [
                'status' => 'dispatched',
                'loading_percent' => 20,
                'actual_fuel_liters' => 32.5,
            ])
            ->assertForbidden();

        $dispatch = $this->userWithRole('DISPATCH');

        $this->actingAs($dispatch)
            ->patch(route('dispatch-orders.update-status', $dispatchOrder), [
                'status' => 'dispatched',
                'loading_percent' => 25,
                'actual_fuel_liters' => 32.5,
                'current_latitude' => 20.8449,
                'current_longitude' => 106.6881,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('dispatch_orders', [
            'id' => $dispatchOrder->id,
            'actual_fuel_liters' => 32.5,
        ]);
        $this->assertDatabaseHas('tracking_logs', [
            'dispatch_order_id' => $dispatchOrder->id,
            'latitude' => 20.8449,
            'longitude' => 106.6881,
        ]);
    }

    public function test_driver_can_be_linked_to_driver_user_account(): void
    {
        $admin = $this->userWithRole('ADMIN');
        $driverUser = $this->userWithRole('DRIVER', ['email' => 'driver.link@example.test']);
        $accountantUser = $this->userWithRole('ACCOUNTANT', ['email' => 'not.driver@example.test']);

        $payload = [
            'full_name' => 'Nguyễn Văn Tài',
            'phone' => '0901234567',
            'date_of_birth' => '21/05/1990',
            'license_number' => 'GPLX-TEST-001',
            'status' => 'active',
            'rank' => 'Tài xế chính',
        ];

        $this->actingAs($admin)
            ->post(route('drivers.store'), $payload + ['user_id' => $accountantUser->id])
            ->assertSessionHasErrors('user_id');

        $this->actingAs($admin)
            ->post(route('drivers.store'), $payload + ['user_id' => $driverUser->id])
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'user_id' => $driverUser->id,
            'full_name' => 'Nguyễn Văn Tài',
            'phone' => '0901234567',
        ]);
    }

    public function test_driver_account_dropdown_only_shows_unlinked_driver_users(): void
    {
        $admin = $this->userWithRole('ADMIN');
        $driverRole = Role::factory()->create([
            'role_code' => 'DRIVER',
            'role_name' => 'DRIVER',
        ]);
        $linkedUser = User::factory()->create([
            'email' => 'linked.driver@example.test',
            'role_id' => $driverRole->id,
        ]);
        $unlinkedUser = User::factory()->create([
            'email' => 'unlinked.driver@example.test',
            'role_id' => $driverRole->id,
        ]);

        Driver::factory()->create([
            'user_id' => $linkedUser->id,
        ]);

        $this->actingAs($admin)
            ->get(route('drivers.index'))
            ->assertOk()
            ->assertViewHas('driverUsers', function ($users) use ($linkedUser, $unlinkedUser): bool {
                return $users->contains('id', $unlinkedUser->id)
                    && ! $users->contains('id', $linkedUser->id);
            });
    }

    public function test_field_staff_can_upload_documents_only_for_active_assignment(): void
    {
        Storage::fake('public');

        $fieldUser = $this->userWithRole('FIELD');
        $location = Location::factory()->create();
        $fieldStaff = FieldStaff::factory()->create([
            'user_id' => $fieldUser->id,
            'responsible_location_id' => $location->id,
            'status' => 'active',
        ]);
        $job = ShippingJob::factory()->create();

        $this->actingAs($fieldUser)
            ->post(route('documents.store'), $this->documentPayload($job))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('documents', 0);

        FieldAssignment::factory()->create([
            'shipping_job_id' => $job->id,
            'field_staff_id' => $fieldStaff->id,
            'location_id' => $location->id,
            'created_by' => $this->userWithRole('DISPATCH')->id,
            'tasks' => [array_key_first(LogisticsOptions::fieldAssignmentTasks())],
            'status' => 'assigned',
        ]);

        $this->actingAs($fieldUser)
            ->post(route('documents.store'), $this->documentPayload($job, 'document-2.pdf'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('documents', [
            'shipping_job_id' => $job->id,
            'doc_category' => 'Biên bản hiện trường',
            'uploaded_by' => $fieldUser->id,
        ]);
    }

    public function test_list_exports_use_distinct_file_templates(): void
    {
        $sales = $this->userWithRole('SALES');

        foreach (['csv', 'xlsx', 'docx', 'pdf'] as $format) {
            $response = $this->actingAs($sales)
                ->get(route('customers.index', ['export' => $format]));

            $response->assertOk();

            $content = $response->streamedContent();

            $this->assertNotEmpty($content);
            $this->assertStringNotContainsString('Ten loai file', $content);

            if ($format === 'csv') {
                $this->assertStringContainsString('Công ty', $content);
                $this->assertStringContainsString('Báo cáo', $content);
            }

            if ($format === 'xlsx') {
                $entries = $this->officeZipEntries($content, [
                    'xl/workbook.xml',
                    'xl/worksheets/sheet1.xml',
                    'xl/styles.xml',
                ]);

                $this->assertStringContainsString('<autoFilter', $entries['xl/worksheets/sheet1.xml']);
                $this->assertStringContainsString('state="frozen"', $entries['xl/worksheets/sheet1.xml']);
                $this->assertLessThan(
                    strpos($entries['xl/worksheets/sheet1.xml'], '<mergeCells'),
                    strpos($entries['xl/worksheets/sheet1.xml'], '<autoFilter'),
                    'Excel requires autoFilter to appear before mergeCells in worksheet XML.'
                );
            }

            if ($format === 'docx') {
                $entries = $this->officeZipEntries($content, [
                    'word/document.xml',
                    'word/_rels/document.xml.rels',
                ]);

                $this->assertStringContainsString('w:tblLayout w:type="fixed"', $entries['word/document.xml']);
                $this->assertStringContainsString('w:tblGrid', $entries['word/document.xml']);
                $this->assertStringContainsString('w:tblW w:w="15038"', $entries['word/document.xml']);
            }

            if ($format === 'pdf') {
                $this->assertStringStartsWith('%PDF', $content);
                $this->assertStringContainsString('Nhan vien xuat', $content);
            }
        }
    }

    /**
     * @param  list<string>  $entryNames
     * @return array<string, string>
     */
    private function officeZipEntries(string $content, array $entryNames): array
    {
        $path = tempnam(sys_get_temp_dir(), 'office-export-');

        file_put_contents($path, $content);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);

        $entries = [];

        foreach ($entryNames as $entryName) {
            $entry = $zip->getFromName($entryName);
            $this->assertNotFalse($entry, "Missing Office entry [{$entryName}].");
            $entries[$entryName] = (string) $entry;
        }

        $zip->close();
        @unlink($path);

        return $entries;
    }

    /**
     * @return array<string, mixed>
     */
    private function documentPayload(ShippingJob $job, string $filename = 'document.pdf'): array
    {
        return [
            'shipping_job_id' => $job->id,
            'doc_category' => 'Biên bản hiện trường',
            'document_flow' => 'input',
            'tax_stage' => 'before_tax',
            'file' => UploadedFile::fake()->create($filename, 12, 'application/pdf'),
        ];
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

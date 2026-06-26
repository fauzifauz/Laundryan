<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Payroll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin to authenticate
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        // Create an employee
        $this->employee = User::create([
            'name' => 'Andi Pratama',
            'email' => 'andi@test.com',
            'password' => bcrypt('password'),
            'role' => 'karyawan',
            'status' => 'active'
        ]);
    }

    /**
     * Test admin can create a new payroll record manually and receives the flashed session variables.
     */
    public function test_admin_can_create_new_payroll_record_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payroll.store'), [
            'user_id' => $this->employee->id,
            'amount' => 5000000,
            'bonus' => 500000,
            'potongan' => 200000,
            'month' => 6,
            'year' => 2026,
            'status' => 'pending',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Payroll record manually created successfully.');
        $response->assertSessionHas('new_payroll_created', true);
        $response->assertSessionHas('new_payroll_employee', 'Andi Pratama');
        $response->assertSessionHas('new_payroll_period', 'June 2026');
        $response->assertSessionHas('new_payroll_net_salary', 'Rp 5,300,000'); // 5,000,000 + 500,000 - 200,000 = 5,300,000
        $response->assertSessionHas('new_payroll_created_at');
    }

    /**
     * Test that creating a duplicate payroll record via store is rejected and returns a warning.
     */
    public function test_creating_duplicate_payroll_record_is_prevented(): void
    {
        // First create a record
        Payroll::create([
            'user_id' => $this->employee->id,
            'amount' => 5000000,
            'bonus' => 0,
            'potongan' => 0,
            'month' => 6,
            'year' => 2026,
            'status' => 'pending',
        ]);

        // Attempting to store it again
        $response = $this->actingAs($this->admin)->post(route('admin.payroll.store'), [
            'user_id' => $this->employee->id,
            'amount' => 5000000,
            'bonus' => 100000,
            'potongan' => 0,
            'month' => 6,
            'year' => 2026,
            'status' => 'pending',
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('success');
        $response->assertSessionHas('warning', 'Payroll record for Andi Pratama in June 2026 already exists.');
        $response->assertSessionHas('toast_title', 'Payroll Already Exists');
    }

    /**
     * Test admin can generate payroll for the first time.
     */
    public function test_admin_can_generate_payroll_for_the_first_time(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payroll.generate'), [
            'month' => 7,
            'year' => 2026,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Payroll generated for July 2026');
        $response->assertSessionMissing('warning');
    }

    /**
     * Test admin generating payroll duplicate returns a warning.
     */
    public function test_admin_generating_payroll_duplicate_returns_warning(): void
    {
        // First generation
        $this->actingAs($this->admin)->post(route('admin.payroll.generate'), [
            'month' => 7,
            'year' => 2026,
        ]);

        // Second duplicate generation
        $response = $this->actingAs($this->admin)->post(route('admin.payroll.generate'), [
            'month' => 7,
            'year' => 2026,
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('success');
        $response->assertSessionHas('warning', 'Payroll for July 2026 has already been generated.');
    }
}

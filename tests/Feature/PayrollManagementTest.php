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

    public function test_employee_salary_page_shows_last_withdrawn_and_all_withdrawable_payrolls(): void
    {
        $withdrawn = Payroll::create([
            'user_id' => $this->employee->id,
            'amount' => 4000000,
            'bonus' => 0,
            'potongan' => 0,
            'month' => 4,
            'year' => 2026,
            'status' => 'paid',
            'payment_method' => 'stripe',
            'payment_date' => now()->subDays(5),
            'stripe_transfer_id' => 'WDL-STRIPE-TEST',
        ]);

        Payroll::create([
            'user_id' => $this->employee->id,
            'amount' => 5000000,
            'bonus' => 0,
            'potongan' => 0,
            'month' => 5,
            'year' => 2026,
            'status' => 'pending',
        ]);

        Payroll::create([
            'user_id' => $this->employee->id,
            'amount' => 5000000,
            'bonus' => 0,
            'potongan' => 0,
            'month' => 6,
            'year' => 2026,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->employee)->get(route('karyawan.salary.index'));

        $response->assertOk();
        $response->assertSee('Current Statement');
        $response->assertSee('April 2026');
        $response->assertSee('Last successfully withdrawn');
        $response->assertSee('May 2026');
        $response->assertSee('June 2026');
        $response->assertSee('PAY-' . sprintf('%04d', $withdrawn->id));
    }

    public function test_employee_can_withdraw_pending_payroll(): void
    {
        $payroll = Payroll::create([
            'user_id' => $this->employee->id,
            'amount' => 5000000,
            'bonus' => 0,
            'potongan' => 0,
            'month' => 6,
            'year' => 2026,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->employee)->post(route('karyawan.salary.withdraw', $payroll), [
            'payment_method' => 'stripe',
            'stripe_account_id' => 'acct_test123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Your salary has been withdrawn successfully.');
        $response->assertSessionHas('toast_title', 'Salary Withdrawn Successfully');
        $this->assertEquals('paid', $payroll->fresh()->status);
    }

    public function test_employee_cannot_withdraw_already_paid_payroll(): void
    {
        $payroll = Payroll::create([
            'user_id' => $this->employee->id,
            'amount' => 5000000,
            'bonus' => 0,
            'potongan' => 0,
            'month' => 6,
            'year' => 2026,
            'status' => 'paid',
            'payment_method' => 'stripe',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->employee)->post(route('karyawan.salary.withdraw', $payroll), [
            'payment_method' => 'stripe',
            'stripe_account_id' => 'acct_test123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This payroll is not available for withdrawal.');
        $response->assertSessionMissing('success');
    }
}

<?php

namespace Tests\Feature\Projects;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDocumentCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_and_invoice_include_customer_when_present(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::query()->create([
            'name' => 'Acme Household',
            'address' => "12 Bridge Street\nBristol",
            'email' => 'billing@acme.test',
            'telephone' => '0117 000 0000',
            'type' => 'domestic',
        ]);

        $project = Project::query()->create([
            'name' => 'Kitchen refresh',
            'description' => 'Replace units',
            'created_by' => $admin->id,
            'leader_id' => $admin->id,
            'customer_id' => $customer->id,
            'project_status' => 'active',
            'volunteer_required' => false,
            'quote_amount' => 450,
            'invoice_amount' => 450,
        ]);

        $this->actingAs($admin)
            ->get(route('projects.quote', $project))
            ->assertOk()
            ->assertSee('Bill to')
            ->assertSee('Acme Household')
            ->assertSee('Domestic')
            ->assertSee('billing@acme.test')
            ->assertSee('0117 000 0000')
            ->assertSee('12 Bridge Street');

        $this->actingAs($admin)
            ->get(route('projects.invoice', $project))
            ->assertOk()
            ->assertSee('Bill to')
            ->assertSee('Acme Household')
            ->assertSee('billing@acme.test');
    }

    public function test_quote_omits_bill_to_when_no_customer(): void
    {
        $admin = User::factory()->admin()->create();

        $project = Project::query()->create([
            'name' => 'Internal job',
            'description' => 'No customer',
            'created_by' => $admin->id,
            'leader_id' => $admin->id,
            'project_status' => 'draft',
            'volunteer_required' => false,
            'quote_amount' => 100,
        ]);

        $this->actingAs($admin)
            ->get(route('projects.quote', $project))
            ->assertOk()
            ->assertDontSee('Bill to');
    }
}

<?php

namespace Tests\Feature\Projects;

use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_customer_on_customers_page(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CustomersIndex::class)
            ->call('openCreateCustomerModal')
            ->set('customer_name', 'Acme Ltd')
            ->set('customer_address', "1 High Street\nTown")
            ->set('customer_email', 'hello@acme.test')
            ->set('customer_telephone', '01234 567890')
            ->set('customer_type', 'commercial')
            ->call('saveCustomer')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'name' => 'Acme Ltd',
            'email' => 'hello@acme.test',
            'telephone' => '01234 567890',
            'type' => 'commercial',
        ]);
    }

    public function test_non_admin_cannot_access_customers_page(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('customers.index'))
            ->assertForbidden();
    }

    public function test_project_can_be_created_with_customer_and_filtered(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::query()->create([
            'name' => 'Jane Householder',
            'email' => 'jane@example.test',
            'telephone' => '07700 900123',
            'address' => '2 Oak Lane',
            'type' => 'domestic',
        ]);
        $other = Customer::query()->create([
            'name' => 'Other Co',
            'type' => 'commercial',
        ]);

        Livewire::actingAs($admin)
            ->test(ProjectsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Kitchen job')
            ->set('description', 'Fit kitchen units')
            ->set('project_status', 'draft')
            ->set('leader_id', $admin->id)
            ->set('customer_id', $customer->id)
            ->call('createProject');

        $project = Project::query()->where('name', 'Kitchen job')->first();

        $this->assertNotNull($project);
        $this->assertSame($customer->id, $project->customer_id);

        Livewire::actingAs($admin)
            ->test(ProjectsIndex::class)
            ->set('customerFilter', (string) $customer->id)
            ->assertSee('Kitchen job')
            ->set('customerFilter', (string) $other->id)
            ->assertDontSee('Kitchen job');
    }

    public function test_project_can_be_created_with_new_customer_step(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(ProjectsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Garden tidy')
            ->set('description', 'Clear overgrowth')
            ->set('project_status', 'draft')
            ->set('leader_id', $admin->id)
            ->call('goToNewCustomerStep')
            ->assertSet('createStep', 2)
            ->set('new_customer_name', 'New Client')
            ->set('new_customer_type', 'domestic')
            ->set('new_customer_email', 'client@example.test')
            ->call('createProject')
            ->assertHasNoErrors();

        $customer = Customer::query()->where('name', 'New Client')->first();
        $project = Project::query()->where('name', 'Garden tidy')->first();

        $this->assertNotNull($customer);
        $this->assertNotNull($project);
        $this->assertSame($customer->id, $project->customer_id);
        $this->assertSame('client@example.test', $customer->email);
    }

    public function test_project_edit_can_update_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $first = Customer::query()->create(['name' => 'First', 'type' => 'domestic']);
        $second = Customer::query()->create(['name' => 'Second', 'type' => 'commercial']);

        $project = Project::query()->create([
            'name' => 'Job',
            'description' => 'Desc',
            'created_by' => $admin->id,
            'leader_id' => $admin->id,
            'customer_id' => $first->id,
            'project_status' => 'draft',
            'volunteer_required' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjectsShow::class, ['project' => $project])
            ->call('openEditModal')
            ->set('customer_id', $second->id)
            ->call('updateProject')
            ->assertHasNoErrors();

        $this->assertSame($second->id, $project->fresh()->customer_id);
    }

    public function test_regular_member_cannot_see_customer_contact_on_project(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $customer = Customer::query()->create([
            'name' => 'Private Client',
            'email' => 'secret@client.test',
            'telephone' => '07700 900999',
            'address' => '9 Hidden Lane',
            'type' => 'domestic',
        ]);

        $project = Project::query()->create([
            'name' => 'Contact privacy job',
            'description' => 'Desc',
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'customer_id' => $customer->id,
            'project_status' => 'outstanding',
            'volunteer_required' => false,
        ]);

        Livewire::actingAs($member)
            ->test(ProjectsShow::class, ['project' => $project])
            ->assertSee('Private Client')
            ->assertSee('9 Hidden Lane')
            ->assertDontSee('secret@client.test')
            ->assertDontSee('07700 900999');
    }

    public function test_project_manager_can_see_customer_contact_on_project(): void
    {
        $manager = User::factory()->create();
        $customer = Customer::query()->create([
            'name' => 'Visible Client',
            'email' => 'visible@client.test',
            'telephone' => '0117 123 4567',
            'type' => 'commercial',
        ]);

        $project = Project::query()->create([
            'name' => 'Managed job',
            'description' => 'Desc',
            'created_by' => $manager->id,
            'leader_id' => $manager->id,
            'customer_id' => $customer->id,
            'project_status' => 'outstanding',
            'volunteer_required' => false,
        ]);

        Livewire::actingAs($manager)
            ->test(ProjectsShow::class, ['project' => $project])
            ->assertSee('Visible Client')
            ->assertSee('visible@client.test')
            ->assertSee('0117 123 4567');
    }

    public function test_project_leader_who_is_not_owner_can_see_customer_contact(): void
    {
        $owner = User::factory()->create();
        $leader = User::factory()->create();
        $customer = Customer::query()->create([
            'name' => 'Leader Client',
            'email' => 'leader-view@client.test',
            'telephone' => '01234 111222',
            'type' => 'domestic',
        ]);

        $project = Project::query()->create([
            'name' => 'Led job',
            'description' => 'Desc',
            'created_by' => $owner->id,
            'leader_id' => $leader->id,
            'customer_id' => $customer->id,
            'project_status' => 'outstanding',
            'volunteer_required' => false,
        ]);

        Livewire::actingAs($leader)
            ->test(ProjectsShow::class, ['project' => $project])
            ->assertSee('leader-view@client.test')
            ->assertSee('01234 111222');
    }

    public function test_customer_select_options_omit_contact_details(): void
    {
        $member = User::factory()->create();
        Customer::query()->create([
            'name' => 'Picker Client',
            'email' => 'picker@client.test',
            'telephone' => '07000 111222',
            'type' => 'commercial',
        ]);

        Livewire::actingAs($member)
            ->test(ProjectsIndex::class)
            ->call('openCreateModal')
            ->assertSee('Picker Client')
            ->assertSee('Commercial')
            ->assertDontSee('picker@client.test')
            ->assertDontSee('07000 111222');
    }
}

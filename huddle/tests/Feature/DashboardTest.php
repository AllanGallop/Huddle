<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectVolunteer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard')->assertOk();
    }

    public function test_dashboard_shows_personalised_project_updates_and_categories(): void
    {
        $user = User::factory()->create(['name' => 'Alex Gallop']);
        $other = User::factory()->create();
        $woodshop = ProjectCategory::create(['name' => 'Woodshop']);
        $health = ProjectCategory::create(['name' => 'H&S']);

        $led = Project::create([
            'name' => 'Workshop benches',
            'description' => 'Build benches',
            'created_by' => $user->id,
            'leader_id' => $user->id,
            'volunteer_required' => false,
            'project_status' => 'in-progress',
        ]);
        $led->categories()->sync([$woodshop->id]);
        $led->forceFill(['updated_at' => now()])->save();

        $volunteered = Project::create([
            'name' => 'Safety signage',
            'description' => 'H&S signs',
            'created_by' => $other->id,
            'leader_id' => $other->id,
            'volunteer_required' => true,
            'project_status' => 'outstanding',
        ]);
        $volunteered->categories()->sync([$health->id]);
        ProjectVolunteer::create([
            'project_id' => $volunteered->id,
            'user_id' => $user->id,
        ]);
        $volunteered->forceFill(['updated_at' => now()])->save();

        $unrelated = Project::create([
            'name' => 'Someone else project',
            'description' => 'Not mine',
            'created_by' => $other->id,
            'leader_id' => $other->id,
            'volunteer_required' => false,
            'project_status' => 'in-progress',
        ]);
        $unrelated->forceFill(['updated_at' => now()])->save();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee(__('Welcome back, :name', ['name' => 'Alex']))
            ->assertSee('Workshop benches')
            ->assertSee('Safety signage')
            ->assertDontSee('Someone else project')
            ->assertSee('Woodshop')
            ->assertSee('H&S')
            ->assertSee(__('Leading'))
            ->assertSee(__('Volunteering'));
    }
}

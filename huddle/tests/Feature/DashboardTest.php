<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Event;
use App\Models\EventVolunteer;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectComment;
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

    public function test_dashboard_shows_activity_updates_categories_and_personal_events(): void
    {
        $user = User::factory()->create(['name' => 'Alex Gallop']);
        $other = User::factory()->create(['name' => 'Sam Other']);
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

        ProjectComment::create([
            'project_id' => $led->id,
            'user_id' => $other->id,
            'comment' => 'Looking good',
        ]);

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

        Project::create([
            'name' => 'Someone else project',
            'description' => 'Not mine',
            'created_by' => $other->id,
            'leader_id' => $other->id,
            'volunteer_required' => false,
            'project_status' => 'in-progress',
        ]);

        $myEvent = Event::create([
            'name' => 'Open day shift',
            'description' => 'Help on the door',
            'location' => 'Main shed',
            'event_type' => 'public',
            'event_status' => 'published',
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHours(3),
            'created_by' => $other->id,
            'volunteer_required' => true,
        ]);
        EventVolunteer::create([
            'event_id' => $myEvent->id,
            'user_id' => $user->id,
        ]);

        $otherEvent = Event::create([
            'name' => 'Committee meeting',
            'description' => 'Monthly meeting',
            'location' => 'Office',
            'event_type' => 'public',
            'event_status' => 'published',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
            'created_by' => $other->id,
            'volunteer_required' => false,
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee(__('Welcome back, :name', ['name' => 'Alex']))
            ->assertSee('Workshop benches')
            ->assertSee(__(':name commented', ['name' => 'Sam Other']))
            ->assertSee('Safety signage')
            ->assertSee(__('You signed up as a volunteer'))
            ->assertDontSee('Someone else project')
            ->assertSee('Woodshop')
            ->assertSee('H&S')
            ->assertSee(__('Leading'))
            ->assertSee('Open day shift')
            ->assertSee('Committee meeting')
            ->assertSeeInOrder(['Open day shift', 'Committee meeting']);
    }
}

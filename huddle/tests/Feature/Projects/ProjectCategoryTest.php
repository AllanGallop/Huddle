<?php

namespace Tests\Feature\Projects;

use App\Livewire\Admin\Index as AdminIndex;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_project_category(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(AdminIndex::class)
            ->call('setTab', 'categories')
            ->call('openCreateCategoryModal')
            ->set('category_name', 'Woodshop')
            ->set('category_description', 'Wood projects')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('project_categories', [
            'name' => 'Woodshop',
            'description' => 'Wood projects',
        ]);
    }

    public function test_project_can_be_created_and_filtered_by_category(): void
    {
        $admin = User::factory()->admin()->create();
        $woodshop = ProjectCategory::create(['name' => 'Woodshop']);
        $health = ProjectCategory::create(['name' => 'H&S']);

        Livewire::actingAs($admin)
            ->test(ProjectsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Bench build')
            ->set('description', 'A workshop bench')
            ->set('project_status', 'draft')
            ->set('leader_id', $admin->id)
            ->set('assignedCategoryIds', [$woodshop->id])
            ->call('createProject');

        $project = Project::query()->where('name', 'Bench build')->first();

        $this->assertNotNull($project);
        $this->assertTrue($project->categories->contains('id', $woodshop->id));
        $this->assertFalse($project->categories->contains('id', $health->id));

        Livewire::actingAs($admin)
            ->test(ProjectsIndex::class)
            ->set('categoryFilter', (string) $woodshop->id)
            ->assertSee('Bench build');

        Livewire::actingAs($admin)
            ->test(ProjectsIndex::class)
            ->set('categoryFilter', (string) $health->id)
            ->assertDontSee('Bench build');
    }

    public function test_project_categories_can_be_updated_on_edit(): void
    {
        $admin = User::factory()->admin()->create();
        $woodshop = ProjectCategory::create(['name' => 'Woodshop']);
        $health = ProjectCategory::create(['name' => 'H&S']);

        $project = Project::create([
            'name' => 'Safety rails',
            'description' => 'Install rails',
            'created_by' => $admin->id,
            'leader_id' => $admin->id,
            'volunteer_required' => false,
            'project_status' => 'outstanding',
        ]);
        $project->categories()->sync([$woodshop->id]);

        Livewire::actingAs($admin)
            ->test(ProjectsShow::class, ['project' => $project])
            ->call('openEditModal')
            ->set('assignedCategoryIds', [$health->id])
            ->call('updateProject')
            ->assertHasNoErrors();

        $project->refresh();

        $this->assertTrue($project->categories->contains('id', $health->id));
        $this->assertFalse($project->categories->contains('id', $woodshop->id));
    }
}

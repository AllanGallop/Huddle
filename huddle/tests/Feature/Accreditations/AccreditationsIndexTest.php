<?php

namespace Tests\Feature\Accreditations;

use App\Livewire\Accreditations\Index;
use App\Models\Accreditation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccreditationsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_by_accreditation_name_and_mentor(): void
    {
        $member = User::factory()->create();
        $mentor = User::factory()->create(['name' => 'Alex Mentor']);

        $firstAid = Accreditation::query()->create([
            'name' => 'First Aid',
            'description' => 'Basic first aid',
            'is_active' => true,
        ]);
        $firstAid->mentors()->attach($mentor);

        Accreditation::query()->create([
            'name' => 'Woodshop',
            'description' => 'Machine training',
            'is_active' => true,
        ]);

        Livewire::actingAs($member)
            ->test(Index::class)
            ->assertSee('First Aid')
            ->assertSee('Woodshop')
            ->set('search', 'First')
            ->assertSee('First Aid')
            ->assertDontSee('Woodshop')
            ->set('search', 'Alex')
            ->assertSee('First Aid')
            ->assertDontSee('Woodshop');
    }

    public function test_multiple_accreditations_can_be_expanded(): void
    {
        $member = User::factory()->create();

        $first = Accreditation::query()->create([
            'name' => 'Alpha',
            'description' => 'Alpha accreditation',
            'is_active' => true,
        ]);
        $second = Accreditation::query()->create([
            'name' => 'Beta',
            'description' => 'Beta accreditation',
            'is_active' => true,
        ]);

        Livewire::actingAs($member)
            ->test(Index::class)
            ->call('toggle', $first->id)
            ->call('toggle', $second->id)
            ->assertSet('expandedIds', [$first->id, $second->id])
            ->call('collapseAll')
            ->assertSet('expandedIds', [])
            ->call('expandAll')
            ->assertSet('expandedIds', [$first->id, $second->id]);
    }

    public function test_mentors_tab_lists_mentors_with_accreditations(): void
    {
        $member = User::factory()->create();
        $mentor = User::factory()->create(['name' => 'Casey Coach']);
        User::factory()->create(['name' => 'Not A Mentor']);

        $firstAid = Accreditation::query()->create([
            'name' => 'First Aid',
            'description' => 'Basic first aid',
            'is_active' => true,
        ]);
        $firstAid->mentors()->attach($mentor);

        Livewire::actingAs($member)
            ->test(Index::class, ['tab' => 'mentors'])
            ->assertSee('Casey Coach')
            ->assertSee('First Aid')
            ->assertDontSee('Not A Mentor')
            ->set('search', 'First')
            ->assertSee('Casey Coach')
            ->set('search', 'Nobody')
            ->assertDontSee('Casey Coach');
    }
}

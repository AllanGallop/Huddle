<?php

namespace Tests\Feature\Wiki;

use App\Livewire\Wiki\Edit;
use App\Models\User;
use App\Models\UserFlags;
use App\Models\WikiDirectory;
use App\Models\WikiPage;
use App\Models\WikiPageVersion;
use App\Services\WikiPageService;
use App\Support\WikiMarkdown;
use App\Support\WikiPathResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WikiTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_edit_wiki(): void
    {
        $member = User::factory()->create(['role_id' => 2]);

        $this->actingAs($member)
            ->get(route('wiki.edit'))
            ->assertForbidden();
    }

    public function test_mentor_can_create_page_with_version_and_wiki_links(): void
    {
        $mentor = User::factory()->create(['role_id' => 2]);
        $mentor->flags()->attach(UserFlags::create(['name' => 'Mentor', 'description' => 'Mentor']));

        $dir = WikiDirectory::create(['name' => 'Guides', 'slug' => 'guides']);

        Livewire::actingAs($mentor)
            ->test(Edit::class)
            ->set('title', 'Setup')
            ->set('slug', 'setup')
            ->set('wiki_directory_id', $dir->id)
            ->set('body', "# Setup\n\nSee [[guides/other|Other page]]\n\n```mermaid\ngraph LR\n  A --> B\n```")
            ->set('change_summary', 'Initial')
            ->call('save')
            ->assertRedirect(route('wiki.show', 'guides/setup'));

        $page = WikiPage::query()->where('slug', 'setup')->first();
        $this->assertNotNull($page);
        $this->assertSame(1, $page->versions()->count());

        $other = app(WikiPageService::class)->create([
            'title' => 'Other',
            'slug' => 'other',
            'wiki_directory_id' => $dir->id,
            'body' => 'Other page body',
        ], $mentor);

        $html = app(WikiMarkdown::class)->toHtml('Link: [[guides/other]]');
        $this->assertStringContainsString($other->url(), $html);
        $this->assertStringContainsString('class="mermaid"', app(WikiMarkdown::class)->toHtml("```mermaid\ngraph TD\n  A-->B\n```"));

        app(WikiPageService::class)->update($page, [
            'title' => 'Setup v2',
            'body' => 'Updated body',
            'change_summary' => 'Second version',
        ], $mentor);

        $this->assertSame(2, $page->fresh()->versions()->count());

        $resolved = app(WikiPathResolver::class)->resolve('guides/setup');
        $this->assertSame(WikiPathResolver::TYPE_PAGE, $resolved['type']);
    }

    public function test_restoring_version_creates_new_revision(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $service = app(WikiPageService::class);

        $page = $service->create([
            'title' => 'Doc',
            'slug' => 'doc',
            'body' => 'Version one',
        ], $admin);

        $service->update($page, [
            'title' => 'Doc',
            'body' => 'Version two',
        ], $admin);

        $first = $page->versions()->where('version_number', 1)->first();

        $service->restoreVersion($page, $first, $admin);

        $this->assertSame(3, $page->fresh()->versions()->count());
        $this->assertSame('Version one', $page->latestVersion->body);
    }
}

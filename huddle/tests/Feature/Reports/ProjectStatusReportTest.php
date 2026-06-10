<?php

namespace Tests\Feature\Reports;

use App\Mail\ProjectStatusReportMail;
use App\Models\ProjectComment;
use App\Models\Project;
use App\Models\ProjectVolunteer;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProjectStatusReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_generator_page_loads_without_query_parameters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Projects status report');
    }

    public function test_projects_status_report_only_includes_outstanding_and_in_progress_projects(): void
    {
        $user = User::factory()->create();
        $leader = User::factory()->create();
        $volunteer = User::factory()->create(['name' => 'Alex Volunteer']);

        $outstanding = $this->createProject($user, $leader, [
            'name' => 'Food bank expansion',
            'description' => "First description line\nSecond description line",
            'project_status' => 'outstanding',
            'financial_status' => 'quoted',
            'quote_amount' => 1200,
            'invoice_amount' => 1500,
            'deposit_amount' => 200,
            'payment_amount' => 100,
        ]);

        ProjectVolunteer::create([
            'project_id' => $outstanding->id,
            'user_id' => $volunteer->id,
        ]);

        $comment = ProjectComment::create([
            'project_id' => $outstanding->id,
            'user_id' => $user->id,
            'parent_comment_id' => null,
            'comment' => 'First report comment',
        ]);

        ProjectComment::create([
            'project_id' => $outstanding->id,
            'user_id' => $leader->id,
            'parent_comment_id' => $comment->id,
            'comment' => 'Reply to report comment',
        ]);

        $inProgress = $this->createProject($user, $leader, [
            'name' => 'Garden volunteers rota',
            'project_status' => 'in-progress',
        ]);

        $draft = $this->createProject($user, $leader, [
            'name' => 'Draft only project',
            'project_status' => 'draft',
        ]);

        $completed = $this->createProject($user, $leader, [
            'name' => 'Completed archive candidate',
            'project_status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get(route('reports.projects-status'))
            ->assertOk()
            ->assertSee($outstanding->name)
            ->assertSee($inProgress->name)
            ->assertDontSee($draft->name)
            ->assertDontSee($completed->name)
            ->assertSee($outstanding->formattedId())
            ->assertSee('Alex Volunteer')
            ->assertSee('1 volunteer')
            ->assertSee('2 comments')
            ->assertDontSee($outstanding->formatMoney($outstanding->quote_amount))
            ->assertDontSee($outstanding->formatMoney($outstanding->invoice_amount))
            ->assertDontSee($outstanding->formatMoney($outstanding->balanceDue()))
            ->assertSee('First description line')
            ->assertSee('Second description line')
            ->assertSee('First report comment')
            ->assertSee('Reply to report comment')
            ->assertDontSee('project included')
            ->assertDontSee('Filters applied');
    }

    public function test_projects_status_report_shows_financials_for_admin_and_committee(): void
    {
        $leader = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $committeeMember = User::factory()->create();
        $committeeTag = UserFlags::create(['name' => 'Committee', 'description' => 'Committee member']);
        $committeeMember->flags()->attach($committeeTag);

        $project = $this->createProject($admin, $leader, [
            'name' => 'Finance visible project',
            'project_status' => 'outstanding',
            'quote_amount' => 2500,
            'invoice_amount' => 3000,
            'financial_status' => 'quoted',
        ]);

        $this->actingAs($admin)
            ->get(route('reports.projects-status'))
            ->assertOk()
            ->assertSee($project->formatMoney($project->quote_amount));

        $this->actingAs($committeeMember)
            ->get(route('reports.projects-status'))
            ->assertOk()
            ->assertSee($project->formatMoney($project->quote_amount));
    }

    public function test_projects_status_report_applies_date_range_and_other_filters(): void
    {
        $user = User::factory()->create();
        $matchingLeader = User::factory()->create(['name' => 'Jordan Lead']);
        $otherLeader = User::factory()->create();

        $matching = $this->createProject($user, $matchingLeader, [
            'name' => 'Filtered match',
            'project_status' => 'in-progress',
            'due_date' => now()->addDays(4)->toDateString(),
            'volunteer_required' => true,
            'financial_status' => 'quoted',
        ]);

        $wrongLeader = $this->createProject($user, $otherLeader, [
            'name' => 'Wrong leader',
            'project_status' => 'in-progress',
            'due_date' => now()->addDays(4)->toDateString(),
            'volunteer_required' => true,
            'financial_status' => 'quoted',
        ]);

        $wrongDate = $this->createProject($user, $matchingLeader, [
            'name' => 'Wrong date',
            'project_status' => 'in-progress',
            'due_date' => now()->addDays(20)->toDateString(),
            'volunteer_required' => true,
            'financial_status' => 'quoted',
        ]);

        $wrongFinancialStatus = $this->createProject($user, $matchingLeader, [
            'name' => 'Wrong finance',
            'project_status' => 'in-progress',
            'due_date' => now()->addDays(4)->toDateString(),
            'volunteer_required' => true,
            'financial_status' => 'paid',
        ]);

        $this->actingAs($user)
            ->get(route('reports.projects-status', [
                'statuses' => ['in-progress'],
                'leader_id' => $matchingLeader->id,
                'due_date_from' => now()->addDays(1)->toDateString(),
                'due_date_to' => now()->addDays(7)->toDateString(),
                'volunteer_filter' => 'required',
                'financial_status' => 'quoted',
            ]))
            ->assertOk()
            ->assertSee($matching->name)
            ->assertDontSee($wrongLeader->name)
            ->assertDontSee($wrongDate->name)
            ->assertDontSee($wrongFinancialStatus->name)
            ->assertSee('Filters applied')
            ->assertSee('Jordan Lead');
    }

    public function test_projects_status_report_pdf_downloads(): void
    {
        $user = User::factory()->create();
        $leader = User::factory()->create();

        $this->createProject($user, $leader, [
            'name' => 'Youth outreach',
            'project_status' => 'outstanding',
        ]);

        $this->actingAs($user)
            ->get(route('reports.projects-status.pdf', [
                'statuses' => ['outstanding'],
                'due_date_from' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_projects_status_report_can_be_emailed(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $leader = User::factory()->create();

        $this->createProject($user, $leader, [
            'name' => 'Community kitchen upgrade',
            'project_status' => 'in-progress',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('reports.projects-status.email'), [
                'email' => 'reports@example.com',
                'statuses' => ['in-progress'],
                'overdue_only' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        Mail::assertSent(ProjectStatusReportMail::class, function (ProjectStatusReportMail $mail): bool {
            return $mail->hasTo('reports@example.com');
        });
    }

    protected function createProject(User $creator, User $leader, array $attributes = []): Project
    {
        return Project::create([
            'name' => 'Project name',
            'description' => 'Project description',
            'created_by' => $creator->id,
            'leader_id' => $leader->id,
            'volunteer_required' => false,
            'project_status' => 'outstanding',
            'due_date' => now()->addWeek()->toDateString(),
            ...$attributes,
        ]);
    }
}

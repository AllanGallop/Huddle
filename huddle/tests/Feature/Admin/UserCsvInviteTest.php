<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Index;
use App\Models\MembershipRenewal;
use App\Models\MembershipRenewalAssignment;
use App\Models\Role;
use App\Models\User;
use App\Models\UserFlags;
use App\Notifications\UserInvitationNotification;
use App\Services\UserCsvInviteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class UserCsvInviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_service_parses_valid_rows(): void
    {
        $memberRole = Role::query()->where('name', 'member')->firstOrFail();
        $mentorTag = UserFlags::create(['name' => 'Mentor', 'description' => 'Mentor']);
        $renewal = MembershipRenewal::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $csv = <<<CSV
name,email,roles,tags,membership
Jane Doe,jane@example.com,member,Mentor,2026
CSV;

        $path = $this->writeTempCsv($csv);
        $result = app(UserCsvInviteService::class)->parse($path);

        $this->assertCount(1, $result['rows']);
        $this->assertSame(1, $result['valid_count']);
        $this->assertTrue($result['rows'][0]['valid']);
        $this->assertSame('Jane Doe', $result['rows'][0]['name']);
        $this->assertSame('jane@example.com', $result['rows'][0]['email']);
        $this->assertSame([$memberRole->id], $result['rows'][0]['role_ids']);
        $this->assertSame([$mentorTag->id], $result['rows'][0]['flag_ids']);
        $this->assertSame($renewal->id, $result['rows'][0]['membership_renewal_id']);
    }

    public function test_csv_service_flags_duplicate_and_existing_emails(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $csv = <<<CSV
name,email,roles,tags,membership
First User,dup@example.com,member,,
Second User,dup@example.com,member,,
Existing User,existing@example.com,member,,
CSV;

        $path = $this->writeTempCsv($csv);
        $result = app(UserCsvInviteService::class)->parse($path);

        $this->assertCount(3, $result['rows']);
        $this->assertTrue($result['rows'][0]['valid']);
        $this->assertFalse($result['rows'][1]['valid']);
        $this->assertFalse($result['rows'][2]['valid']);
        $this->assertStringContainsString('Duplicate email', $result['rows'][1]['errors'][0]);
        $this->assertStringContainsString('already exists', $result['rows'][2]['errors'][0]);
    }

    public function test_admin_can_invite_users_via_csv(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $memberRole = Role::query()->where('name', 'member')->firstOrFail();
        $committeeTag = UserFlags::create(['name' => 'Committee', 'description' => 'Committee']);
        $renewal = MembershipRenewal::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $csv = <<<CSV
name,email,roles,tags,membership
Alice Smith,alice@example.com,member,Committee,2026
Bob Jones,bob@example.com,member,,2026
CSV;

        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('openCsvInviteModal')
            ->set('csvInviteUpload', UploadedFile::fake()->createWithContent('invites.csv', $csv))
            ->assertSet('csvInviteShowPreview', true)
            ->call('confirmCsvInvite')
            ->assertHasNoErrors();

        $alice = User::query()->where('email', 'alice@example.com')->first();
        $bob = User::query()->where('email', 'bob@example.com')->first();

        $this->assertNotNull($alice);
        $this->assertNotNull($bob);
        $this->assertTrue($alice->hasRole('member'));
        $this->assertTrue($alice->hasFlag('Committee'));
        $this->assertTrue(
            MembershipRenewalAssignment::query()
                ->where('user_id', $alice->id)
                ->where('membership_renewal_id', $renewal->id)
                ->exists()
        );
        $this->assertTrue(
            MembershipRenewalAssignment::query()
                ->where('user_id', $bob->id)
                ->where('membership_renewal_id', $renewal->id)
                ->exists()
        );

        Notification::assertSentTo($alice, UserInvitationNotification::class);
        Notification::assertSentTo($bob, UserInvitationNotification::class);
    }

    public function test_admin_csv_import_skips_invalid_rows(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        $csv = <<<CSV
name,email,roles,tags,membership
Valid User,valid@example.com,member,,
Bad User,not-an-email,member,,
CSV;

        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('openCsvInviteModal')
            ->set('csvInviteUpload', UploadedFile::fake()->createWithContent('invites.csv', $csv))
            ->call('confirmCsvInvite')
            ->assertHasNoErrors();

        $this->assertNotNull(User::query()->where('email', 'valid@example.com')->first());
        $this->assertNull(User::query()->where('email', 'not-an-email')->first());
        Notification::assertCount(1);
    }

    private function writeTempCsv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv-invite-');
        file_put_contents($path, $contents);

        return $path;
    }
}

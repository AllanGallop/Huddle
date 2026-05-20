<?php

namespace App\Console\Commands;

use App\Mail\CommunityDigestMail;
use App\Services\CommunityDigestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCommunityDigestCommand extends Command
{
    protected $signature = 'digest:send
                            {--user= : Send to a single user ID only}
                            {--force : Send even when there is nothing new}';

    protected $description = 'Send the community email digest to opted-in users';

    public function handle(CommunityDigestService $digests): int
    {
        $users = $digests->recipients();

        if ($userId = $this->option('user')) {
            $users = $users->where('id', (int) $userId);

            if ($users->isEmpty()) {
                $this->error(__('User not found or has opted out of digests.'));

                return self::FAILURE;
            }
        }

        $sent = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $digest = $digests->buildFor($user);

            if (! $digest->hasContent() && ! $this->option('force')) {
                $skipped++;
                $this->line("Skipped {$user->email} (nothing new).");

                continue;
            }

            Mail::to($user)->send(new CommunityDigestMail($user, $digest));

            $user->forceFill(['last_digest_sent_at' => now()])->save();

            $sent++;
            $this->info("Sent digest to {$user->email}");
        }

        $this->info("Done. Sent: {$sent}, skipped: {$skipped}.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\UsernameGenerator;
use Illuminate\Console\Command;

class BackfillUsernames extends Command
{
    protected $signature = 'users:backfill-usernames';
    protected $description = 'Assign a generated username to any user that does not already have one';

    public function handle(): int
    {
        $count = 0;

        User::whereNull('username')->chunkById(500, function ($users) use (&$count) {
            foreach ($users as $user) {
                $username = UsernameGenerator::fromName($user->name);
                $user->update(['username' => $username]);
                $this->line("User #{$user->id}: assigned username '{$username}'");
                $count++;
            }
        });

        $count === 0
            ? $this->info('No users needed a username backfilled.')
            : $this->info("Backfilled {$count} user(s).");

        return self::SUCCESS;
    }
}
<?php

namespace App\Console\Commands;

use App\Models\Users;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateInviteCodes extends Command
{
    protected $signature = 'generate:invite-codes {--length=8 : Length of the generated invite codes}';

    protected $description = 'Generate unique invite codes for users who do not have one yet';

    public function handle()
    {
        $length = (int) $this->option('length');
        $users = Users::whereNull('invite_code')->get();
        $count = 0;

        foreach ($users as $user) {
            do {
                $code = Str::upper(Str::random($length));
            } while (Users::where('invite_code', $code)->exists());

            $user->invite_code = $code;
            $user->save();
            $count++;
        }

        $this->info("Generated invite codes for {$count} users using {$length}-character codes.");
    }
}

<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('platform:admin {email}', function (string $email): void {
    $user = User::where('email', $email)->first();

    if ($user === null) {
        $this->error("No user found with email [{$email}].");

        return;
    }

    $user->forceFill(['is_platform_admin' => true])->save();

    $this->info("{$user->email} is now a platform administrator.");
})->purpose('Grant platform administrator access to an existing user');

<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create {--name=} {--email=} {--password=}', function () {
    $name = $this->option('name') ?: $this->ask('Admin name', 'Administrator');
    $email = $this->option('email') ?: $this->ask('Admin email');
    $password = $this->option('password') ?: $this->secret('Admin password (min 8 characters)');

    $validator = Validator::make(
        ['name' => $name, 'email' => $email, 'password' => $password],
        [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]
    );

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }

    User::updateOrCreate(
        ['email' => $email],
        ['name' => $name, 'password' => $password]
    );

    $this->info('Admin account ready: '.$email);

    return 0;
})->purpose('Create or update an admin account for the student management area');

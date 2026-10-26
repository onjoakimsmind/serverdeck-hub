<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'hub:create-admin {email} {name}';

    protected $description = 'Create a Hub login (there is no public registration)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->argument('name');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name],
            ['email' => ['required', 'email'], 'name' => ['required', 'string', 'max:80']],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");

            return self::FAILURE;
        }

        $password = $this->secret('Password (input hidden)');
        $confirm = $this->secret('Confirm password');

        if ($password === null || $password === '' || $password !== $confirm) {
            $this->error('Passwords did not match or were empty.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Created Hub login for {$email}.");

        return self::SUCCESS;
    }
}

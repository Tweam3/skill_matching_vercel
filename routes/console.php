<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('send-mail', function () {
    $apiKey = config('services.mailtrap.token') ?? env('MAILTRAP_API_TOKEN');

    if (! $apiKey) {
        $this->error('MAILTRAP_API_TOKEN is not set.');
        return 1;
    }

    $response = Http::withToken($apiKey)
        ->withHeaders(['Content-Type' => 'application/json'])
        ->post('https://send.api.mailtrap.io/api/send', [
            'from' => [
                'email' => config('mail.from.address'),
                'name' => config('mail.from.name'),
            ],
            'to' => [
                ['email' => 'batm6800@gmail.com'],
            ],
            'subject' => 'You are awesome!',
            'category' => 'Integration Test',
            'text' => 'Congrats for sending test email with Mailtrap!',
        ]);

    if ($response->successful()) {
        $this->info('Mail sent via Mailtrap.');
        $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
    } else {
        $this->error('Mailtrap send failed (HTTP '.$response->status().').');
        $this->line($response->body());
        return 1;
    }
})->purpose('Send a test email via Mailtrap');

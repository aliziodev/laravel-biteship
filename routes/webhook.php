<?php

use Aliziodev\Biteship\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('biteship.webhook.middleware', ['api']))
    ->post(config('biteship.webhook.path', 'biteship/webhook'), [WebhookController::class, 'handle'])
    ->name('biteship.webhook');

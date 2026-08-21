<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Cashier\Events\WebhookReceived;

class LogWebhookReceived implements ShouldQueue
{
    public function handle(WebhookReceived $event): void
    {
        logger()->info('Stripe webhook processed via queue', [
            'type' => $event->payload['type'] ?? 'unknown',
        ]);
    }
}
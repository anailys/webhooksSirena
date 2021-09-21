<?php

namespace App\Handler;

use Spatie\WebhookClient\WebhookProcessor;

class WebhookHandler extends WebhookProcessor
{
    public function handle()
    {
        logger('I was here');
        logger($this->webhookCall);
    }
}

<?php

namespace Engelsystem\Events\Events;

use Engelsystem\Events\Event;
use Engelsystem\Models\Message;

class MessageCreated extends Event
{
    protected ?string $name = 'message.created';

    public function __construct(public Message $message)
    {
    }
}

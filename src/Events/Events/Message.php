<?php

namespace Engelsystem\Events\Events;

use Engelsystem\Events\Event;
use Engelsystem\Models\Message as MessageModel;

class Message extends Event
{
    public static string $CREATED = 'message.created';
    protected ?string $name = 'message.created';

    public function __construct(public MessageModel $message)
    {
    }
}

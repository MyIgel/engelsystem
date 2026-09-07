<?php

namespace Engelsystem\Events;

use Engelsystem\Models\Message as MessageModel;

class Message extends Event
{
    public static string $CREATED = 'message.created';

    public function __construct(protected string $name, public MessageModel $message)
    {
    }
}

<?php

namespace Engelsystem\Events;

use Engelsystem\Models\Message as MessageModel;

class Message extends ModelEvent
{
    public static string $CREATED = 'message.created';

    public function __construct(protected string $name, public MessageModel $message)
    {
        parent::__construct($this->name, $message);
    }
}

<?php

namespace Engelsystem\Events;


/**
 * Empty event, used when no data is needed
 */
class NullEvent extends Event
{
    public function __construct(protected ?string $name = null)
    {
    }
}

<?php

namespace Engelsystem\Events;

class Event
{
    public function getName(): string
    {
        if (property_exists($this, 'name') && $this->name) {
            return $this->name;
        }

        return get_class($this);
    }
}

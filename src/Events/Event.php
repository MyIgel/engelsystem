<?php

namespace Engelsystem\Events;

use Psr\EventDispatcher\StoppableEventInterface;

class Event implements StoppableEventInterface
{
    protected bool $propagationStopped = false;

    public function getName(): string
    {
        if (property_exists($this, 'name') && $this->name) {
            return $this->name;
        }

        return get_class($this);
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}

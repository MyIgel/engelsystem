<?php

namespace Engelsystem\Events;

use Psr\EventDispatcher\StoppableEventInterface;

class Event implements StoppableEventInterface
{
    protected bool $propagationStopped = false;
    protected ?string $name = null;

    public function getName(): string
    {
        // TODO: load dynamic property?
        return $this->name;
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

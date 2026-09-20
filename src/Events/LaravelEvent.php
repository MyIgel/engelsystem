<?php

namespace Engelsystem\Events;


class LaravelEvent extends StoppableEvent
{
    protected bool $propagationStopped = false;

    public function __construct(protected string $name, public mixed $payload = [], protected bool $haltable = false)
    {
    }

    public function stopPropagation(): void
    {
        if (!$this->haltable) {
            return;
        }

        parent::stopPropagation();
    }
}

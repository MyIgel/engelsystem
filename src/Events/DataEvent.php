<?php

namespace Engelsystem\Events;


/**
 * Used when basic data is used
 * @deprecated
 */
class DataEvent extends Event
{
    public function __construct(string $name, protected array $data)
    {
        $this->name = $name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}

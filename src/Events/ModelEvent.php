<?php

namespace Engelsystem\Events;

use Engelsystem\Models\BaseModel;

class ModelEvent extends Event
{
    public static string $CREATED = 'model.created';
    public static string $UPDATED = 'model.updated';
    public static string $DELETED = 'model.deleted';

    public function __construct(protected string $name, public BaseModel $model, public BaseModel|null $oldModel = null)
    {
    }
}

<?php

namespace Engelsystem\Events;

use Illuminate\Support\Collection;

class OAuth2 extends Event
{
    public static string $LOGIN = 'oauth2.login';

    public function __construct(protected string $name, public string $providerName, public Collection $userdata)
    {
    }
}

<?php

namespace Engelsystem\Events;

use ArrayAccess;

class OAuth2 extends Event
{
    public static string $LOGIN = 'oauth2.login';

    public function __construct(protected string $name, public string $providerName, public ArrayAccess $userdata)
    {
    }
}

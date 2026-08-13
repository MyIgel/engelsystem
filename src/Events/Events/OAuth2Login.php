<?php

namespace Engelsystem\Events\Events;

use Engelsystem\Events\Event;

class OAuth2Login extends Event
{
    protected ?string $name = 'oauth2.login';

}

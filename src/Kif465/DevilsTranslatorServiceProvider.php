<?php

namespace Engelsystem\Kif465;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Helpers\Translator;

class DevilsTranslatorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->alias(DevilsTranslator::class, Translator::class);
    }
}

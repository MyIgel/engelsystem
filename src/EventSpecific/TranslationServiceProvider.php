<?php

namespace Engelsystem\EventSpecific;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Helpers\Translation\Translator as EngelsystemTranslator;

class TranslationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->alias(Translator::class, EngelsystemTranslator::class);
    }
}

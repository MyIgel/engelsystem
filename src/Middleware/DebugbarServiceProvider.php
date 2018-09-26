<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DebugBar as Bar;
use DebugBar\StandardDebugBar;
use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Helpers\TwigTracer;
use Engelsystem\Helpers\TwigTracerDataCollector;
use PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware as DebugbarMiddleware;

class DebugbarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');

        if (
            $config->get('environment') == 'development'
            && class_exists(DebugbarMiddleware::class)
        ) {
            $this->app->resolving(Debugbar::class, function (Debugbar $debugbar, Application $container): void {
                /** @var StandardDebugBar $bar */
                $bar = $container->make(StandardDebugBar::class);

                $container->instance(Bar::class, $bar);
                $container->instance(StandardDebugBar::class, $bar);

                $debugbarRenderer = $bar->getJavascriptRenderer('/phpdebugbar');

                /** @var DebugbarMiddleware $middleware */
                $middleware = $container->make(DebugbarMiddleware::class, [$debugbarRenderer]);

                $debugbar->setDebugbar($bar);
                $debugbar->setDebugbarMiddleware($middleware);

                $container->instance(DebugbarMiddleware::class, $middleware);
                $container->instance('debugbar', $middleware);

                $this->addCollector($bar, PDOCollector::class);

                /** @var \Twig\Environment $twigEnv */
                $twigEnv = $this->app->get('twig.environment');
                /** @var TwigTracer $tracer */
                $tracer = $this->app->make(TwigTracer::class);
                $twigEnv->addExtension($tracer);
                $this->app->instance(TwigTracer::class, $tracer);

                /** @var TwigTracerDataCollector $collector */
                $collector = $this->app->make(TwigTracerDataCollector::class);
                $bar->addCollector($collector);
            });
        }
    }

    protected function addCollector(Bar $debugbar, callable|string $collector): void
    {
        $pdoCollector = $this->app->make($collector);
        $debugbar->addCollector($pdoCollector);
    }
}

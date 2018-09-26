<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Twig\Profiler\Profile;

class TwigTracerDataCollector extends DataCollector implements Renderable, AssetProvider
{
    public function __construct(protected TwigTracer $twig)
    {
    }

    public function collect(): array
    {
        $templates = array();
        $accuRenderTime = 0;

        //dd($this->twig, $this->twig->profile);

        foreach ($this->twig->profile as $tpl) {
            /** @var Profile $tpl */
            $accuRenderTime += $tpl->getDuration();
            $templates[] = array(
                'name' => $tpl->getName(),
                'render_time' => $tpl->getDuration(),
                'render_time_str' => $this->formatDuration($tpl->getDuration()),
            );
        }

        return array(
            'nb_templates' => count($templates),
            'templates' => $templates,
            'accumulated_render_time' => $accuRenderTime,
            'accumulated_render_time_str' => $this->formatDuration($accuRenderTime),
        );
    }

    public function getName(): string
    {
        return 'twig';
    }

    public function getAssets(): array
    {
        return array(
            'twig' => array(
                'icon' => 'leaf',
                'widget' => 'PhpDebugBar.Widgets.TemplatesWidget',
                'map' => 'twig',
                'default' => json_encode(array('templates' => array())),
            ),
            'twig:badge' => array(
                'map' => 'twig.nb_templates',
                'default' => 0,
            ),
        );
    }

    public function getWidgets(): array
    {
        return [
            //"icon" => "code",
            //"tooltip" => "Twig",
            //"map" => "twig.accumulated_render_time",
            //"default" => "",

            'twig' => array(
                'css' => 'widgets/templates/widget.css',
                'js' => 'widgets/templates/widget.js',
            ),
        ];
    }
}

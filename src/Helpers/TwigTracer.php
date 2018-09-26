<?php

declare(strict_types=1);

/*
 * This file is part of the DebugBar package.
 *
 * (c) 2017 Tim Riemenschneider
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Engelsystem\Helpers;

use DebugBar\DataCollector\TimeDataCollector;

/**
 * Class TimeableTwigExtensionProfiler
 *
 * Extends Twig_Extension_Profiler to add rendering times to the TimeDataCollector
 *
 * @package DebugBar\Bridge\Twig
 */
class TwigTracer extends \Twig\Extension\ProfilerExtension
{
    private \DebugBar\DataCollector\TimeDataCollector|null $timeDataCollector;

    public function setTimeDataCollector(TimeDataCollector $timeDataCollector): void
    {
        $this->timeDataCollector = $timeDataCollector;
    }

    public function __construct(public \Twig\Profiler\Profile $profile, ?TimeDataCollector $timeDataCollector = null)
    {
        parent::__construct($profile);

        $this->timeDataCollector = $timeDataCollector;
    }

    public function enter(\Twig\Profiler\Profile $profile): void
    {
        if ($this->timeDataCollector && $profile->isTemplate()) {
            $this->timeDataCollector->startMeasure($profile->getName(), 'template ' . $profile->getName());
        }
        parent::enter($profile);
    }

    public function leave(\Twig\Profiler\Profile $profile): void
    {
        parent::leave($profile);
        if ($this->timeDataCollector && $profile->isTemplate()) {
            $this->timeDataCollector->stopMeasure($profile->getName());
        }
    }
}

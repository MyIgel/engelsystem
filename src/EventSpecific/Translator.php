<?php

namespace Engelsystem\EventSpecific;

use Engelsystem\Helpers\Translation\Translator as EngelsystemTranslator;

class Translator extends EngelsystemTranslator
{
    /** @var array */
    protected $replacements = [
        // EN
        'Angeltypes' => 'Daemontypes',
        'angeltypes' => 'daemontypes',
        'Angeltype'  => 'Daemontype',
        'Angels'     => 'Daemons',
        'angels'     => 'daemons',
        'Angel'      => 'Daemon',
        'Heaven'     => 'Hell',
        'helpers'    => 'daemons',
        'helper'     => 'daemon',

        // DE
        'Engeltypen' => 'Dämonarten',
        'Engeltyp'   => 'Dämonart',
        'Engel'      => 'Dämonen',
        'den Himmel' => 'die Hölle',
        'Himmel'     => 'Hölle',
        'Helfer'     => 'Dämonen',
    ];

    /**
     * @param string $text
     * @param array  $replace
     *
     * @return mixed|string
     */
    protected function replaceText(string $text, array $replace = [])
    {
        $text = parent::replaceText($text, $replace);

        $text = str_replace(array_keys($this->replacements), array_values($this->replacements), $text);

        return $text;
    }
}

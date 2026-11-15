<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Illuminate\Support\Str;

class Assets
{
    protected string $manifestFile = 'manifest.json';

    /**
     * @param string $assetsPath Directory containing assets
     */
    public function __construct(protected string $assetsPath)
    {
    }

    public function getAssetPath(string $asset): string
    {
        $manifest = $this->assetsPath . DIRECTORY_SEPARATOR . $this->manifestFile;
        if (is_readable($manifest)) {
            $manifest = json_decode(file_get_contents($manifest), true);

            if (isset($manifest[$asset])) {
                $asset = $manifest[$asset];
            }
        }

        return $asset;
    }

    public function getPluginAssetPath(string $asset): string
    {
        list($name, $asset) = explode(':', $asset, 2);

        if (Str::startsWith($asset, 'assets/')) {
            $asset = Str::replaceFirst('assets/', '', $asset);
        }

        return $name . '/' . $asset;
    }
}

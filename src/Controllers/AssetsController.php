<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Application;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Plugins\Plugin;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class AssetsController extends BaseController
{
    public function __construct(
        protected Application $app,
        protected Response $response,
    ) {
    }

    public function pluginAsset(Request $request): Response
    {
        $pluginName = $request->getAttribute('plugin');
        $asset = $request->getAttribute('asset');

        $activePlugins = $this->app->getTags()['plugin'] ?? [];
        if (!in_array($pluginName, $activePlugins)) {
            throw new HttpNotFound();
        }

        /** @var Plugin $plugin */
        $plugin = $this->app->get($pluginName);
        $path = $plugin->getPath();
        $assetsPath = implode(DIRECTORY_SEPARATOR, [$path, 'assets']);
        $assetFile = realpath(implode(DIRECTORY_SEPARATOR, [$assetsPath, $asset]));
        $fileIsInPluginAssetDir = Str::startsWith($assetFile, realpath($assetsPath) . DIRECTORY_SEPARATOR);
        if (
            !$fileIsInPluginAssetDir
            || !file_exists($assetFile)
            || !is_readable($assetFile)
        ) {
            throw new HttpNotFound();
        }

        $ext = pathinfo($assetFile, PATHINFO_EXTENSION) ?? 'bin';
        $mimeTypes = MimeTypes::getDefault()->getMimeTypes($ext);
        $mimeType = $mimeTypes[0] ?? 'application/octet-stream';
        $content = file_get_contents($assetFile);

        return $this->response
            ->withHeader('Content-Type', $mimeType)
            ->withContent($content);
    }
}

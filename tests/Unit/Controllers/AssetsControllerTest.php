<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\AssetsController;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Plugins\Plugin;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(AssetsController::class, '__construct')]
#[CoversMethod(AssetsController::class, 'pluginAsset')]
class AssetsControllerTest extends TestCase
{
    public function testPluginAsset(): void
    {
        $request = (new Request())
            ->withAttribute('plugin', 'TestPlugin')
            ->withAttribute('asset', 'blubb.css');
        $plugin = $this->setUpPlugin();

        $controller = new AssetsController($this->app, new Response());

        $response = $controller->pluginAsset($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/css', $response->getHeader('Content-Type')[0] ?? null);
        $this->assertEquals(
            file_get_contents($plugin->getPath() . '/assets/blubb.css'),
            $response->getContent(),
        );
    }

    public function testPluginAssetBlob(): void
    {
        $request = (new Request())
            ->withAttribute('plugin', 'TestPlugin')
            ->withAttribute('asset', 'other/some-unknown-blob');
        $plugin = $this->setUpPlugin();

        $controller = new AssetsController($this->app, new Response());

        $response = $controller->pluginAsset($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/octet-stream', $response->getHeader('Content-Type')[0] ?? null);
        $this->assertEquals(
            file_get_contents($plugin->getPath() . '/assets/other/some-unknown-blob'),
            $response->getContent(),
        );
    }

    public function testPluginAssetPluginNotFound(): void
    {
        $request = (new Request())
            ->withAttribute('plugin', 'nope')
            ->withAttribute('asset', 'unavailable.txt');

        $controller = new AssetsController($this->app, new Response());

        $this->expectException(HttpNotFound::class);
        $controller->pluginAsset($request);
    }

    public function testPluginAssetAssetNotFound(): void
    {
        $request = (new Request())
            ->withAttribute('plugin', 'TestPlugin')
            ->withAttribute('asset', 'not-existing.js');
        $this->setUpPlugin();

        $controller = new AssetsController($this->app, new Response());

        $this->expectException(HttpNotFound::class);
        $controller->pluginAsset($request);
    }

    public function testPluginAssetTraversalNotFound(): void
    {
        $request = (new Request())
            ->withAttribute('plugin', 'TestPlugin')
            ->withAttribute('asset', '../secret.txt');
        $this->setUpPlugin();

        $controller = new AssetsController($this->app, new Response());

        $this->expectException(HttpNotFound::class);
        $controller->pluginAsset($request);
    }

    protected function setUpPlugin(): Plugin
    {
        $plugin = new Plugin([
            'name' => 'test/plugin',
            'plugin_name' => 'TestPlugin',
            'path' => __DIR__ . '/Stub/TestPlugin',
            'namespace' => 'Test\\Plugin',
            'namespace_path' => __DIR__ . '/Stub/TestPlugin/src',
        ]);
        $this->app->instance('TestPlugin', $plugin);
        $this->app->tag('TestPlugin', 'plugin');

        return $plugin;
    }
}

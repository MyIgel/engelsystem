<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Illuminate\Support\Str;

class UiController extends BaseController
{
    public array $permissions = [
        'api'
    ];

    protected string $path = __DIR__ . '/../../../vendor/swagger-api/swagger-ui/dist/';

    public function __construct(protected UrlGeneratorInterface $url, protected Response $response)
    {
    }

    public function resource(Request $request): Response
    {
        $mime = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'text/javascript',
            'png' => 'image/png',
        ];
        $file = $request->getAttribute('file') ?? 'index.html';
        $file = ltrim($file, '/');
        $extension = pathinfo($file)['extension'];
        $path = $this->path . $file;
        if (
            Str::contains($file, '/')
            || !isset($mime[$extension])
            || !file_exists($path)
        ) {
            throw new HttpNotFound();
        }

        $content = file_get_contents($path);
        $content = match ($file) {
            'index.html' => $this->handleIndex($content),
            'swagger-initializer.js' => $this->handleConfig($content),
            default => $content,
        };

        return $this->response
            ->withHeader('content-type', $mime[$extension])
            ->withContent($content);
    }

    protected function handleIndex(string $content): string
    {
        $content = str_replace('"./', '"', $content);
        $content = str_replace('href="', 'href="' . $this->url->to('/api-docs/'), $content);
        return str_replace('src="', 'src="' . $this->url->to('/api-docs/'), $content);
    }

    protected function handleConfig(string $content): string
    {
        return preg_replace(
            '/".+swagger.json"/',
            '"' . $this->url->to('/api/v0-beta/openapi') . '"',
            $content
        );
    }
}

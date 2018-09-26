<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar as Bar;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware as DebugbarMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class Debugbar implements MiddlewareInterface, RequestHandlerInterface
{
    protected ?DebugbarMiddleware $debugbarMiddleware = null;

    protected ?RequestHandlerInterface $handler;

    protected ?Bar $debugbar;

    protected ?ServerRequestInterface $request;

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->debugbarMiddleware) {
            return $handler->handle($request);
        }

        $this->request = $request;
        if ($request instanceof Request) {
            /** @var PsrHttpFactory $factory */
            $factory = app(PsrHttpFactory::class);
            $request = $factory->createRequest($request);
        }

        $this->handler = $handler;
        $response = $this->debugbarMiddleware->process($request, $this);
        return $response->withHeader(
            'Content-Security-Policy',
            $response->getHeaderLine('Content-Security-Policy')
            . "; script-src 'self' 'unsafe-inline';"
        );
    }

    /**
     * Handle the request and return a response.
     *
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var TimeDataCollector $time */
        $time = $this->debugbar->getCollector('time');
        $time->startMeasure('loop');

        $response = $this->handler->handle($this->request);

        $time->stopMeasure('loop');

        $contentType = $response->getHeaderLine('Content-Type');
        if (
            empty($contentType)
            && strpos((string) $response->getBody(), '<html') !== false
        ) {
            $response = $response->withHeader('Content-Type', 'text/html');
        }

        if ($response instanceof Response) {
            /** @var PsrHttpFactory $factory */
            $factory = app(PsrHttpFactory::class);
            $response = $factory->createResponse($response);
        }

        return $response;
    }

    public function setDebugbar(Bar $debugbar): void
    {
        $this->debugbar = $debugbar;
    }

    public function setDebugbarMiddleware(DebugbarMiddleware $debugbar): void
    {
        $this->debugbarMiddleware = $debugbar;
    }
}

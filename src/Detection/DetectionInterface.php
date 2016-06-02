<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Detection;

use GuzzleHttp\Message\RequestInterface as RequestI;
use GuzzleHttp\Message\ResponseInterface as ResponseI;

/**
 * Implementations should decide if the request/response cycle
 * if erroneous scenario or not. If true library will break the
 * circuit.
 */
interface DetectionInterface
{
    /**
     * @param RequestI        $request
     * @param ResponseI|null  $response
     * @param \Exception|null $exception
     *
     * @return bool
     */
    public function isErroneous(RequestI $request, ResponseI $response = null, \Exception $exception = null);
}

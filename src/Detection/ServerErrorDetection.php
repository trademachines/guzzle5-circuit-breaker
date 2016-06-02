<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Detection;

use GuzzleHttp\Message\RequestInterface as RequestI;
use GuzzleHttp\Message\ResponseInterface as ResponseI;

/**
 * Returns true if the status code of the response is a server error code.
 */
class ServerErrorDetection implements DetectionInterface
{
    /** {@inheritdoc} **/
    public function isErroneous(RequestI $request, ResponseI $response = null, \Exception $exception = null)
    {
        return $response && $response->getStatusCode() >= 500;
    }
}

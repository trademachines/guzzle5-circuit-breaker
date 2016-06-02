<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Detection;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Message\RequestInterface as RequestI;
use GuzzleHttp\Message\ResponseInterface as ResponseI;

/**
 * Returns true if a timeout is detected.
 */
class TimeoutDetection implements DetectionInterface
{
    /** {@inheritdoc} **/
    public function isErroneous(RequestI $request, ResponseI $response = null, \Exception $exception = null)
    {
        return ($exception && $exception instanceof ConnectException);
    }
}

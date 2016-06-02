<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests\Behaviour;

use GuzzleHttp\Event\AbstractEvent;
use GuzzleHttp\Message\ResponseInterface;

class InterceptableEvent extends AbstractEvent 
{
    private $interceptedResponse;
    
    public function intercept(ResponseInterface $response)
    {
        $this->interceptedResponse = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getInterceptedResponse()
    {
        return $this->interceptedResponse;
    }
}

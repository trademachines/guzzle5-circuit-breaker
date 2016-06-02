<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Behaviour;

use GuzzleHttp\Event\EventInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * This behaviour will always return the same response in case
 * of an error. Unfortunately there is no interface for checking
 * if we are able to intercept an event, so we have to rely on
 * ``method_exists``.
 */
class FixedResponseBehaviour implements BehaviourInterface
{
    /** @var ResponseInterface */
    private $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /** {@inheritdoc} **/
    public function act(EventInterface $event)
    {
        if (method_exists($event, 'intercept')) {
            $event->intercept($this->response);
        }
    }
}

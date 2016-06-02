<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Behaviour;

use GuzzleHttp\Event\EventInterface;

/**
 * This is a noop behaviour. In case of an error, an exception
 * is thrown anyway.
 */
class NoopBehaviour implements BehaviourInterface
{
    /** {@inheritdoc} **/
    public function act(EventInterface $event)
    {
        // noop
    }
}

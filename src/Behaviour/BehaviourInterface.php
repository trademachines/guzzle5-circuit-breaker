<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Behaviour;

use GuzzleHttp\Event\EventInterface;

/**
 * Will be called in case of an error to do magic stuff
 * for that event. Remind that it will be called for every
 * request when the state is erroneous.
 */
interface BehaviourInterface
{
    /**
     * @param EventInterface $event
     * 
     * @return void
     */
    public function act(EventInterface $event);
}

<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Behaviour;

use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\EventInterface;
use Trademachines\Guzzle5\CircuitBreaker\Exception\CircuitBreakerException;

class ThrowCircuitBreakerExceptionBehaviour implements BehaviourInterface
{
    const DEFAULT_EXCEPTION_MESSAGE = 'The request was failing.';

    private $exceptionMessage = self::DEFAULT_EXCEPTION_MESSAGE;

    /** {@inheritdoc} **/
    public function act(EventInterface $event)
    {
        if ($event instanceof ErrorEvent) {
            $ex = new CircuitBreakerException($this->exceptionMessage, 0, $event->getException());
        } else {
            $ex = new CircuitBreakerException($this->exceptionMessage);
        }

        throw $ex;
    }

    /**
     * @param string $exceptionMessage
     */
    public function setExceptionMessage($exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;
    }
}

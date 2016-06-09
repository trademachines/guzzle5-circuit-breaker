<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests\Behaviour;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Transaction;
use Trademachines\Guzzle5\CircuitBreaker\Behaviour\ThrowCircuitBreakerExceptionBehaviour;
use Trademachines\Guzzle5\CircuitBreaker\Exception\CircuitBreakerException;

class ThrowCircuitBreakerExceptionBehaviourTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Trademachines\Guzzle5\CircuitBreaker\Exception\CircuitBreakerException
     */
    public function testThrowException()
    {
        $behaviour = new ThrowCircuitBreakerExceptionBehaviour();
        $behaviour->act(new InterceptableEvent());
    }

    public function testUsePreviousException()
    {
        $previous = null;

        $behaviour = new ThrowCircuitBreakerExceptionBehaviour();
        $transaction = new Transaction(
            $this->prophesize(ClientInterface::class)->reveal(),
            $this->prophesize(RequestInterface::class)->reveal()
        );
        $transaction->exception = $previous;

        try {
            $behaviour->act(new ErrorEvent($transaction));
        } catch (CircuitBreakerException $ex) {
            $previous = $ex->getPrevious();
        }

        self::assertSame($transaction->exception, $previous);
    }

    /**
     * @expectedException \Trademachines\Guzzle5\CircuitBreaker\Exception\CircuitBreakerException
     * @expectedExceptionMessage More meaningful message
     */
    public function testPersonalizeExceptionMessage()
    {
        $behaviour = new ThrowCircuitBreakerExceptionBehaviour();
        $behaviour->setExceptionMessage('More meaningful message');
        $behaviour->act(new InterceptableEvent());
    }
}

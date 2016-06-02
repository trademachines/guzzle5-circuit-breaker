<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests;

use Doctrine\Common\Cache\ArrayCache;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Transaction;
use Prophecy\Argument;
use Trademachines\Guzzle5\CircuitBreaker\Behaviour\BehaviourInterface;
use Trademachines\Guzzle5\CircuitBreaker\CircuitBreaker;
use Trademachines\Guzzle5\CircuitBreaker\Detection\DetectionInterface;
use Trademachines\Guzzle5\CircuitBreaker\State;

class CircuitBreakerTest extends \PHPUnit_Framework_TestCase
{
    public function testOverwriteSettingIfNotPresent()
    {
        $breaker = $this->getCircuitBreaker();
        $breaker->getConfigSettings()->set('foo', 'bar');

        $request = $this->getRequest();
        $event   = new BeforeEvent($this->getTransaction($request));
        $breaker->onBefore($event);

        $config = $request->getConfig()->toArray();

        self::assertArraySubset(['foo' => 'bar'], $config);
    }

    public function testDontOverwriteSettingsIfPresent()
    {
        $breaker = $this->getCircuitBreaker();
        $breaker->getConfigSettings()->set('foo', 'bar');

        $request = $this->getRequest(['foo' => 'not-overwritten']);
        $event   = new BeforeEvent($this->getTransaction($request));
        $breaker->onBefore($event);

        $config = $request->getConfig()->toArray();

        self::assertArraySubset(['foo' => 'not-overwritten'], $config);
    }

    public function testStopPropagationIfStateIsNotOk()
    {
        $breaker = $this->getCircuitBreaker(null, null, $this->getState(false));
        $event   = new BeforeEvent($this->getTransaction());
        $breaker->onBefore($event);

        self::assertTrue($event->isPropagationStopped());
    }

    public function testCallBehaviourIfStateIsNotOk()
    {
        $behaviour = $this->getBehaviour();
        $breaker   = $this->getCircuitBreaker(null, $behaviour->reveal(), $this->getState(false));
        $event     = new BeforeEvent($this->getTransaction());
        $breaker->onBefore($event);

        $behaviour->act($event)->shouldHaveBeenCalled();
    }

    public function testDetectErroneousEvent()
    {
        $detection = $this->getDetection();
        $breaker   = $this->getCircuitBreaker($detection->reveal());
        $event     = new ErrorEvent($this->getTransaction());
        $breaker->onError($event);

        $detection->isErroneous(Argument::any(), Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }

    public function testSetStateToNotOkOnError()
    {
        $state   = $this->getState();
        $breaker = $this->getCircuitBreaker($this->getDetection(true)->reveal(), null, $state);
        $event   = new ErrorEvent($this->getTransaction());
        $breaker->onError($event);

        self::assertFalse($state->isOk());
    }

    public function testStopPropagationIfEventIsErroneous()
    {
        $breaker = $this->getCircuitBreaker($this->getDetection(true)->reveal());
        $event   = new ErrorEvent($this->getTransaction());
        $breaker->onError($event);

        self::assertTrue($event->isPropagationStopped());
    }

    public function testCallBehaviourIfEventIsErroneous()
    {
        $behaviour = $this->getBehaviour();
        $breaker   = $this->getCircuitBreaker($this->getDetection(true)->reveal(), $behaviour->reveal());
        $event     = new ErrorEvent($this->getTransaction());
        $breaker->onError($event);

        $behaviour->act($event)->shouldHaveBeenCalled();
    }

    private function getDetection($erroneous = false)
    {
        $detection = $this->prophesize(DetectionInterface::class);
        $detection->isErroneous(Argument::any(), Argument::any(), Argument::any())->willReturn($erroneous);

        return $detection;
    }

    private function getBehaviour()
    {
        return $this->prophesize(BehaviourInterface::class);
    }

    private function getState($ok = true)
    {
        $state = new State(new ArrayCache(), 0);
        $state->ok($ok);

        return $state;
    }

    private function getClient()
    {
        return $this->prophesize(ClientInterface::class);
    }

    private function getRequest(array $options = [])
    {
        return new Request('GET', '/', [], null, $options);
    }

    private function getCircuitBreaker($detection = null, $behaviour = null, $state = null)
    {
        return new CircuitBreaker(
            $detection ?: $this->getDetection()->reveal(),
            $behaviour ?: $this->getBehaviour()->reveal(),
            $state ?: $this->getState()
        );
    }

    private function getTransaction(RequestInterface $request = null)
    {
        return new Transaction(
            $this->getClient()->reveal(),
            $request ?: $this->getRequest()
        );
    }
}

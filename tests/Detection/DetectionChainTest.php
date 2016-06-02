<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests\Detection;

use GuzzleHttp\Message\RequestInterface;
use Prophecy\Argument;
use Trademachines\Guzzle5\CircuitBreaker\Detection\DetectionChain;
use Trademachines\Guzzle5\CircuitBreaker\Detection\DetectionInterface;

class DetectionChainTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnTrueIfErrorIsDetected()
    {
        $first   = $this->prophesize(DetectionInterface::class);
        $chain   = new DetectionChain([$first->reveal()]);

        $first->isErroneous(Argument::any(), Argument::any(), Argument::any())->willReturn(true);

        self::assertTrue($chain->isErroneous($this->prophesize(RequestInterface::class)->reveal()));
    }

    public function testReturnFalseIfNoErrorIsDetected()
    {
        $first   = $this->prophesize(DetectionInterface::class);
        $chain   = new DetectionChain([$first->reveal()]);

        self::assertFalse($chain->isErroneous($this->prophesize(RequestInterface::class)->reveal()));
    }

    public function testDontCallAllDetectionsIfOneIsTrue()
    {
        $first   = $this->prophesize(DetectionInterface::class);
        $second  = $this->prophesize(DetectionInterface::class);
        $chain   = new DetectionChain([$first->reveal(), $second->reveal()]);
        $first->isErroneous(Argument::any(), Argument::any(), Argument::any())->willReturn(true);

        $chain->isErroneous($this->prophesize(RequestInterface::class)->reveal());

        $second->isErroneous(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }
}

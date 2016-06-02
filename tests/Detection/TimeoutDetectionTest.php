<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests\Detection;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Message\RequestInterface;
use Trademachines\Guzzle5\CircuitBreaker\Detection\TimeoutDetection;

class TimeoutDetectionTest extends \PHPUnit_Framework_TestCase
{
    public function testNotErroneousIfNoException()
    {
        self::assertFalse(
            (new TimeoutDetection())->isErroneous($this->prophesize(RequestInterface::class)->reveal())
        );
    }

    public function testNotErroneousIfNotConnectException()
    {
        self::assertFalse(
            (new TimeoutDetection())->isErroneous(
                $this->prophesize(RequestInterface::class)->reveal(),
                null,
                new \Exception()
            )
        );
    }

    public function testErroneousIfConnectException()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        self::assertTrue(
            (new TimeoutDetection())->isErroneous(
                $request,
                null,
                new ConnectException('', $request)
            )
        );
    }
}

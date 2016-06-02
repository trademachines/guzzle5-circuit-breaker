<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests\Detection;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\Response;
use Trademachines\Guzzle5\CircuitBreaker\Detection\ServerErrorDetection;

class ServerErrorDetectionTest extends \PHPUnit_Framework_TestCase
{
    public function testNotErroneousIfNoResponse()
    {
        self::assertFalse(
            (new ServerErrorDetection())->isErroneous($this->prophesize(RequestInterface::class)->reveal())
        );
    }

    public function testNotErroneousIfNoStatusLessThan500()
    {
        self::assertFalse(
            (new ServerErrorDetection())->isErroneous(
                $this->prophesize(RequestInterface::class)->reveal(),
                new Response(499)
            )
        );
    }

    public function testErroneousIfStatusAtLeast500()
    {
        self::assertTrue(
            (new ServerErrorDetection())->isErroneous(
                $this->prophesize(RequestInterface::class)->reveal(),
                new Response(500)
            )
        );
    }
}

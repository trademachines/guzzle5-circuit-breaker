<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests\Behaviour;

use GuzzleHttp\Message\Response;
use Trademachines\Guzzle5\CircuitBreaker\Behaviour\FixedResponseBehaviour;

class FixedResponseBehaviourTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnGivenResponse()
    {
        $response  = new Response(200);
        $behaviour = new FixedResponseBehaviour($response);
        $event     = new InterceptableEvent();

        $behaviour->act($event);

        self::assertSame($response, $event->getInterceptedResponse());
    }
}

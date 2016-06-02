<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests;

use Trademachines\Guzzle5\CircuitBreaker\CircuitBreaker;
use Trademachines\Guzzle5\CircuitBreaker\CircuitBreakerBuilder;

class CircuitBreakerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You need to specify a namespace for your cache
     */
    public function testNeedStateCacheNamespace()
    {
        CircuitBreakerBuilder::create()->build();
    }

    public function testMergeConfigSettings()
    {
        $breaker = CircuitBreakerBuilder::create()
            ->setStateCacheNamespace('test')
            ->setConfigSettings(['foo' => 'bar'])
            ->build();

        self::assertArraySubset(['foo' => 'bar'], $breaker->getConfigSettings()->toArray());
    }

    public function testClientHasBreakerAttached()
    {
        $client = CircuitBreakerBuilder::create()
            ->setStateCacheNamespace('test')
            ->buildClient();

        $emitter  = $client->getEmitter();
        $listener = array_unique(
            array_values(
                array_map(
                    function ($v) {
                        return $v[0][0];
                    },
                    $emitter->listeners()
                )
            ),
            SORT_REGULAR
        );

        self::assertCount(1, $listener);
        self::assertInstanceOf(CircuitBreaker::class, $listener[0]);
    }
}

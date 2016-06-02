<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Trademachines\Guzzle5\CircuitBreaker\State;

class StateTest extends \PHPUnit_Framework_TestCase
{
    public function testIsOkIfKeyIsNotInCache()
    {
        $state = new State($this->cache());

        self::assertStateOk($state);
    }

    public function testIsNotOkIfKeyIsNotInCache()
    {
        $state = new State($this->cache([State::ERRONEOUS_CACHE_KEY => 1]));

        self::assertStateNotOk($state);
    }

    public function testSetOk()
    {
        $state = new State($this->cache());
        $state->ok(true);

        self::assertStateOk($state);
    }

    public function testSetNotOk()
    {
        $state = new State($this->cache());
        $state->ok(false);

        self::assertStateNotOk($state);
    }

    private function cache(array $data = [])
    {
        $cache = new ArrayCache();

        foreach ($data as $k => $v) {
            $cache->save($k, $v);
        }

        return $cache;
    }

    private static function assertStateNotOk(State $state)
    {
        self::assertFalse($state->isOk(), 'State should NOT be ok, but it is');
    }

    private static function assertStateOk(State $state)
    {
        self::assertTrue($state->isOk(), 'State should be ok, but isn\'t');
    }
}

<?php

namespace Trademachines\Guzzle5\CircuitBreaker;

use Doctrine\Common\Cache\Cache;

class State
{
    const ERRONEOUS_CACHE_TTL = 120;
    const ERRONEOUS_CACHE_KEY = 'erroneous';

    /** @var Cache */
    private $cache;

    /** @var int */
    private $ttl;

    public function __construct(Cache $cache, $ttl = null)
    {
        $this->cache = $cache;
        $this->ttl   = $ttl ?: self::ERRONEOUS_CACHE_TTL;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return !$this->cache->contains(self::ERRONEOUS_CACHE_KEY);
    }

    /**
     * Set the state.
     * 
     * @param bool $ok
     */
    public function ok($ok = true)
    {
        $ok = (bool) $ok;

        if ($ok === true) {
            $this->cache->delete(self::ERRONEOUS_CACHE_KEY);
        } else {
            $this->cache->save(self::ERRONEOUS_CACHE_KEY, 1, $this->ttl);
        }
    }
}

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
     * @param string|null $name
     *
     * @return bool
     */
    public function isOk($name = null)
    {
        return !$this->cache->contains($this->getCacheKey($name));
    }

    /**
     * Set the state.
     *
     * @param bool        $ok
     * @param string|null $name
     */
    public function ok($ok, $name = null)
    {
        $ok  = (bool) $ok;
        $key = $this->getCacheKey($name);

        if ($ok === true) {
            $this->cache->delete($key);
        } else {
            $this->cache->save($key, 1, $this->ttl);
        }
    }

    protected function getCacheKey($name = null)
    {
        return $name
            ? $name . '.' . self::ERRONEOUS_CACHE_KEY
            : self::ERRONEOUS_CACHE_KEY;
    }
}

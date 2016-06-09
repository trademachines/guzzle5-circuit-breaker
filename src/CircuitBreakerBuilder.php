<?php

namespace Trademachines\Guzzle5\CircuitBreaker;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Collection;
use Psr\Log\LoggerInterface;
use Trademachines\Guzzle5\CircuitBreaker\Behaviour\BehaviourInterface;
use Trademachines\Guzzle5\CircuitBreaker\Behaviour\NoopBehaviour;
use Trademachines\Guzzle5\CircuitBreaker\Detection\DetectionChain;
use Trademachines\Guzzle5\CircuitBreaker\Detection\DetectionInterface;
use Trademachines\Guzzle5\CircuitBreaker\Detection\ServerErrorDetection;
use Trademachines\Guzzle5\CircuitBreaker\Detection\TimeoutDetection;

/**
 * Provide convenient way of creating an instance of CircuitBreaker
 * with sane defaults. The only things that needs to be done is setting
 * a namespace with setStateCacheNamespace.
 */
final class CircuitBreakerBuilder
{
    /**
     * @param string|null $name
     * 
     * @return static
     */
    public static function create($name = null)
    {
        $builder = new static();
        $builder->name = $name;

        return $builder;
    }

    /** @var string */
    private $name;
    
    /** @var DetectionInterface */
    private $detection;

    /** @var BehaviourInterface */
    private $behaviour;

    /** @var Cache */
    private $stateCache;

    /** @var int */
    private $stateCacheTtl;

    /** @var string */
    private $stateCacheNamespace;

    /** @var Collection|array */
    private $configSettings = [];

    /** @var LoggerInterface */
    private $logger;
    
    /**
     * @return CircuitBreaker
     * @throws \InvalidArgumentException
     */
    public function build()
    {
        $breaker = new CircuitBreaker(
            $this->detection ?: $this->getDefaultDetection(),
            $this->behaviour ?: $this->getDefaultBehaviour(),
            new State(
                $this->stateCache ?: $this->getDefaultStateCache(),
                $this->stateCacheTtl
            )
        );
        $breaker->getConfigSettings()->merge($this->configSettings);

        if ($this->name) {
            $breaker->setName($this->name);
        }
        
        if ($this->logger) {
            $breaker->setLogger($this->logger);
        }

        return $breaker;
    }

    /**
     * @param array $config
     *
     * @return ClientInterface
     */
    public function buildClient(array $config = [])
    {
        $client = new Client($config);
        $client->getEmitter()->attach($this->build());

        return $client;
    }

    /**
     * @return DetectionInterface
     */
    protected function getDefaultDetection()
    {
        $detection = new DetectionChain();
        $detection->addDetection(new TimeoutDetection());
        $detection->addDetection(new ServerErrorDetection());

        return $detection;
    }

    /**
     * @return BehaviourInterface
     */
    protected function getDefaultBehaviour()
    {
        return new NoopBehaviour();
    }

    /**
     * @return Cache
     * @throws \InvalidArgumentException
     */
    protected function getDefaultStateCache()
    {
        $namespace = $this->stateCacheNamespace;

        if (!$namespace) {
            throw new \InvalidArgumentException('You need to specify a namespace for your cache.');
        }

        $cache = $this->getApcCache() ?: $this->getPhpFileCache();
        $cache->setNamespace('cb_' . md5(__DIR__) . '_' . $namespace . '_'); // to avoid collisions

        return $cache;
    }

    /**
     * @param DetectionInterface $detection
     *
     * @return $this
     */
    public function setDetection(DetectionInterface $detection = null)
    {
        $this->detection = $detection;

        return $this;
    }

    /**
     * @param BehaviourInterface $behaviour
     *
     * @return $this
     */
    public function setBehaviour(BehaviourInterface $behaviour)
    {
        $this->behaviour = $behaviour;

        return $this;
    }

    /**
     * @param Cache $stateCache
     *
     * @return $this
     */
    public function setStateCache(Cache $stateCache)
    {
        $this->stateCache = $stateCache;

        return $this;
    }

    /**
     * @param int $stateCacheTtl
     *
     * @return $this
     */
    public function setStateCacheTtl($stateCacheTtl)
    {
        $this->stateCacheTtl = (int) $stateCacheTtl;

        return $this;
    }

    /**
     * @param array|Collection $configSettings
     *
     * @return $this
     */
    public function setConfigSettings($configSettings)
    {
        $this->configSettings = $configSettings;

        return $this;
    }

    /**
     * Sets a namespace for the cache. This string will become part of our own
     * generated namespace to avoid further collisions.
     *
     * @param string $stateCacheNamespace
     *
     * @return $this
     */
    public function setStateCacheNamespace($stateCacheNamespace)
    {
        $this->stateCacheNamespace = $stateCacheNamespace;

        return $this;
    }

    /**
     * @param LoggerInterface|null $logger
     * 
     * @return $this
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        
        return $this;
    }

    private function getApcCache()
    {
        $apcEnabled = ini_get('apc.enabled') || ('cli' === PHP_SAPI && ini_get('apc.enable_cli'));

        if ($apcEnabled && function_exists('apcu_fetch')) {
            return new ApcuCache();
        }

        return null;
    }

    private function getPhpFileCache()
    {
        return new PhpFileCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(__CLASS__));
    }
}

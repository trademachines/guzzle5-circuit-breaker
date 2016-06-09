<?php

namespace Trademachines\Guzzle5\CircuitBreaker;

use GuzzleHttp\Collection;
use GuzzleHttp\Event\AbstractRequestEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\EventInterface;
use GuzzleHttp\Event\SubscriberInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Trademachines\Guzzle5\CircuitBreaker\Behaviour\BehaviourInterface;
use Trademachines\Guzzle5\CircuitBreaker\Detection\DetectionInterface;

class CircuitBreaker implements SubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var DetectionInterface */
    private $detection;

    /** @var BehaviourInterface */
    private $behaviour;

    /** @var State */
    private $state;

    /** @var Collection */
    private $configSettings;

    private static $DEFAULT_CONFIG_SETTINGS = [
        'connect_timeout' => 1,
        'timeout'         => 2,
    ];

    public function __construct(DetectionInterface $detection, BehaviourInterface $behaviour, State $state)
    {
        $this->detection      = $detection;
        $this->behaviour      = $behaviour;
        $this->state          = $state;
        $this->configSettings = new Collection(self::$DEFAULT_CONFIG_SETTINGS);
    }

    /** {@inheritdoc} **/
    public function getEvents()
    {
        return [
            'before' => ['onBefore', 'first'],
            'error'  => ['onError', 'first'],
        ];
    }

    public function onBefore(AbstractRequestEvent $event)
    {
        $this->addConfigSettings($event->getRequest()->getConfig());

        if (!$this->state->isOk()) {
            $this->handleErroneousState($event);
        }
    }

    public function onError(ErrorEvent $event)
    {
        if ($this->isErroneousEvent($event)) {
            $this->state->ok(false);
            $this->handleErroneousState($event);
            $this->logErroneousState($event);
        }
    }

    private function addConfigSettings(Collection $config)
    {
        foreach ($this->configSettings as $k => $v) {
            if (!$config->hasKey($k)) {
                $config->set($k, $v);
            }
        }
    }

    private function handleErroneousState(EventInterface $event)
    {
        $event->stopPropagation();
        $this->behaviour->act($event);

        return false;
    }

    /**
     * @return Collection
     */
    public function getConfigSettings()
    {
        return $this->configSettings;
    }

    private function isErroneousEvent($event)
    {
        $exception = null;

        if ($event instanceof ErrorEvent) {
            $exception = $event->getException();
        }

        return $this->detection->isErroneous($event->getRequest(), $event->getResponse(), $exception);
    }

    private function logErroneousState(ErrorEvent $event)
    {
        if (!$this->logger) {
            return;
        }

        $url = $event->getTransferInfo(CURLINFO_EFFECTIVE_URL);
        $this->logger->critical(sprintf('Call to %s failed.', $url));
    }
}

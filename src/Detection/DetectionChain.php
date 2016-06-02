<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Detection;

use GuzzleHttp\Message\RequestInterface as RequestI;
use GuzzleHttp\Message\ResponseInterface as ResponseI;

/**
 * Chain multiple detections returning true if one of the
 * underlying ones is true.
 */
class DetectionChain implements DetectionInterface
{
    /** @var array|DetectionInterface[] */
    private $chain = [];

    /**
     * DetectionChain constructor.
     *
     * @param array|DetectionInterface[] $detections
     */
    public function __construct(array $detections = [])
    {
        foreach ($detections as $detector) {
            $this->addDetection($detector);
        }
    }

    /**
     * @param DetectionInterface $detection
     */
    public function addDetection(DetectionInterface $detection)
    {
        $this->chain[] = $detection;
    }
    
    /** {@inheritdoc} **/
    public function isErroneous(RequestI $request, ResponseI $response = null, \Exception $exception = null)
    {
        foreach ($this->chain as $detector) {
            if ($detector->isErroneous($request, $response, $exception)) {
                return true;
            }
        }

        return false;
    }
}

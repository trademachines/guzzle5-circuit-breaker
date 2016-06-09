<?php

namespace Trademachines\Guzzle5\CircuitBreaker\Tests;

use Psr\Log\AbstractLogger;

class BufferedLogger extends AbstractLogger
{
    private $messages = [];

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $this->messages[] = [
            'level'   => $level,
            'message' => $message,
            'content' => $context,
        ];
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}

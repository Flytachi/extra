<?php

namespace Extra\Src\Unit\Postman\Event;

class PostmanEventTest implements PostmanEventInterface
{
    private array $exec;

    /**
     * @param string ...$exec
     */
    public function __construct(string ...$exec)
    {
        $this->exec = $exec;
    }

    public function meta(): array
    {
        return [
            'listen' => 'test',
            'script' => [
                'exec' => $this->exec,
                'type' => 'text/javascript'
            ]
        ];
    }
}
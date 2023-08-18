<?php

namespace Extra\Src\Annotation\Postman\Event;

class PostmanEventPreRequest implements PostmanEventInterface
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
            'listen' => 'prerequest',
            'script' => [
                'exec' => $this->exec,
                'type' => 'text/javascript'
            ]
        ];
    }
}
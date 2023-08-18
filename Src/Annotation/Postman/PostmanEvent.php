<?php

namespace Extra\Src\Annotation\Postman;

use Attribute;
use Extra\Src\Annotation\Postman\Event\PostmanEventInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class PostmanEvent implements Postman
{
    private PostmanEventInterface $event;

    /**
     * @param PostmanEventInterface $event
     */
    public function __construct(PostmanEventInterface $event)
    {
        $this->event = $event;
    }

    public function prepare(array &$arrayData): void {
        $arrayData['event'][] = $this->event->meta();
    }

    public static function morph(self ...$events): array
    {
        $result = [];
        foreach ($events as $event) $event->prepare($result);
        return $result;
    }

}
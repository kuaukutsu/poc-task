<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

interface EventSubscriberInterface
{
    /**
     * @return array<class-string<Event>, callable(Event $name, EventInterface $event):void>
     */
    public function subscriptions(): array;
}

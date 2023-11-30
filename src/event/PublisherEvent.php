<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use Throwable;

trait PublisherEvent
{
    /**
     * @var array<class-string<Event>, array<int, callable(Event $name, EventInterface $event):void>>
     */
    private array $eventHandlers = [];

    final public function on(EventSubscriberInterface $subscriber): void
    {
        $subscriberHash = spl_object_id($subscriber);
        foreach ($subscriber->subscriptions() as $name => $callback) {
            $this->eventHandlers[$name][$subscriberHash] = $callback;
        }
    }

    final public function off(EventSubscriberInterface $subscriber): void
    {
        $subscriberHash = spl_object_id($subscriber);
        foreach (Event::cases() as $event) {
            if (array_key_exists($event->value, $this->eventHandlers)) {
                unset($this->eventHandlers[$event->value][$subscriberHash]);
            }
        }
    }

    private function trigger(Event $name, EventInterface $event): void
    {
        if (array_key_exists($name->value, $this->eventHandlers)) {
            foreach ($this->eventHandlers[$name->value] as $subscriberCallback) {
                try {
                    $subscriberCallback($name, $event);
                } catch (Throwable) {
                }
            }
        }
    }
}

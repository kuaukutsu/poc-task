<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tools;

use kuaukutsu\poc\task\event\Event;
use kuaukutsu\poc\task\event\EventInterface;
use kuaukutsu\poc\task\event\EventSubscriberInterface;
use kuaukutsu\poc\task\event\StageEvent;

final class TaskManagerOutput implements EventSubscriberInterface
{
    public function subscriptions(): array
    {
        $subscriptions = [];
        foreach (Event::cases() as $event) {
            $subscriptions[$event->value] = $this->trace(...);
        }

        $subscriptions[Event::StageSuccess->value] = $this->traceProcessSuccess(...);
        $subscriptions[Event::StageError->value] = $this->traceProcessError(...);

        /**
         * @var array<class-string<Event>, callable(Event $name, EventInterface $event):void>
         */
        return $subscriptions;
    }

    public function trace(Event $name, EventInterface $event): void
    {
        match ($name) {
            Event::StagePush => $this->stdout('push: ' . $event->getMessage()),
            Event::StagePull => $this->stdout('pull: ' . $event->getMessage()),
            Event::StageStop => $this->stdout('stop: ' . $event->getMessage()),
            Event::StageTimeout => $this->stdout('timeout: ' . $event->getMessage()),
            default => $this->stdout($event->getMessage())
        };
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function traceProcessSuccess(Event $name, StageEvent $event): void
    {
        $this->stdout("success: [{$event->getUuid()}] " . $event->getOutput());
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function traceProcessError(Event $name, StageEvent $event): void
    {
        $this->stdout("error: [{$event->getUuid()}] " . $event->getOutput());
    }

    private function stdout(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }
}

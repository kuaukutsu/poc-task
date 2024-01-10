<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tools;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\event\Event;
use kuaukutsu\poc\task\event\EventInterface;
use kuaukutsu\poc\task\event\EventSubscriberInterface;
use kuaukutsu\poc\task\event\ProcessEvent;

final class TaskManagerOutput implements EventSubscriberInterface
{
    public function __construct(private readonly ConsoleOutputInterface $output)
    {
    }

    public function subscriptions(): array
    {
        $subscriptions = [];
        foreach (Event::cases() as $event) {
            $subscriptions[$event->value] = $this->trace(...);
        }

        $subscriptions[Event::ProcessSuccess->value] = $this->traceProcessSuccess(...);

        /**
         * @var array<class-string<Event>, callable(Event $name, EventInterface $event):void>
         */
        return $subscriptions;
    }

    public function trace(Event $name, EventInterface $event): void
    {
        match ($name) {
            Event::ProcessPush => $this->stdout('push: ' . $event->getMessage()),
            Event::ProcessPull => $this->stdout('pull: ' . $event->getMessage()),
            Event::ProcessStop => $this->stdout('stop: ' . $event->getMessage()),
            Event::ProcessDelay => $this->stdout('delay: ' . $event->getMessage()),
            Event::ProcessTimeout => $this->stdout('timeout: ' . $event->getMessage()),
            Event::ProcessException => $this->stdout('exception: ' . $event->getMessage()),
            default => $this->stdout($event->getMessage()),
        };
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function traceProcessSuccess(Event $name, ProcessEvent $event): void
    {
        $this->stdout("success: [{$event->getUuid()}] " . $event->getOutput());
    }

    private function stdout(string $message): void
    {
        $this->output->writeln($message);
    }
}

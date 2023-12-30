<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use RuntimeException;
use DateTimeImmutable;
use Revolt\EventLoop;
use Revolt\EventLoop\UnsupportedFeatureException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use kuaukutsu\poc\task\event\Event;
use kuaukutsu\poc\task\event\EventPublisherInterface;
use kuaukutsu\poc\task\event\LoopTickEvent;
use kuaukutsu\poc\task\event\LoopTimeoutEvent;
use kuaukutsu\poc\task\event\LoopExceptionEvent;
use kuaukutsu\poc\task\event\PublisherEvent;
use kuaukutsu\poc\task\event\ProcessEvent;
use kuaukutsu\poc\task\event\ProcessTimeoutEvent;
use kuaukutsu\poc\task\event\ProcessContextEvent;
use kuaukutsu\poc\task\event\ProcessExceptionEvent;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\processing\TaskProcess;
use kuaukutsu\poc\task\processing\TaskProcessContext;
use kuaukutsu\poc\task\processing\TaskProcessFactory;
use kuaukutsu\poc\task\processing\TaskProcessing;

final class TaskManager implements EventPublisherInterface
{
    use PublisherEvent;

    /**
     * @var array<non-empty-string, TaskProcess>
     */
    private array $processesActive = [];

    /**
     * @var array<non-empty-string, TaskProcessContext>
     */
    private array $processesDelay = [];

    /**
     * A unique identifier that can be used to cancel, enable or disable the callback.
     */
    private ?string $runnerId = null;

    /**
     * A unique identifier that can be used to cancel, enable or disable the callback.
     */
    private ?string $keeperId = null;

    private bool $isCheckForgotten = false;

    public function __construct(
        private readonly TaskProcessing $processing,
        private readonly TaskProcessFactory $processFactory,
    ) {
    }

    /**
     * @throws UnsupportedFeatureException
     */
    public function run(TaskManagerOptions $options = new TaskManagerOptions()): void
    {
        $this->runnerId = EventLoop::repeat(
            $options->getHeartbeat(),
            function () use ($options): void {
                $this->trigger(
                    Event::LoopTick,
                    new LoopTickEvent(
                        count($this->processesActive),
                        count($this->processesDelay),
                    )
                );

                try {
                    $this->processing->loadTaskProcess($options);
                } catch (ProcessingException $exception) {
                    $this->trigger(
                        Event::LoopException,
                        new LoopExceptionEvent($exception)
                    );
                }

                $this->processRun($options);

                if ($this->isCheckForgotten && $this->processesActive === []) {
                    $this->isCheckForgotten = false;
                    try {
                        $this->processing->loadTaskProcessForgotten($options);
                    } catch (ProcessingException $exception) {
                        $this->trigger(
                            Event::LoopException,
                            new LoopExceptionEvent($exception)
                        );
                    }
                }
            }
        );

        $this->keeperId = EventLoop::repeat(
            $options->getKeeperInterval(),
            function () use ($options): void {
                if ($this->processesActive === [] && $this->processesDelay === []) {
                    $this->keeperDisable();
                    return;
                }

                foreach ($this->processesDelay as $context) {
                    $this->processDelayActive($context, $options);
                }

                foreach ($this->processesActive as $process) {
                    if ($process->isRunning() === false) {
                        $this->trigger(
                            $process->isSuccessful() ? Event::ProcessSuccess : Event::ProcessError,
                            new ProcessEvent($process)
                        );

                        try {
                            $process->isSuccessful()
                                ? $this->processing->next($process)
                                : $this->processing->cancel($process);
                        } catch (ProcessingException $exception) {
                            $this->trigger(
                                Event::ProcessException,
                                new ProcessExceptionEvent($process, $exception)
                            );
                        }

                        $this->processPull($process);
                        unset($process);
                        continue;
                    }

                    try {
                        $process->checkTimeout();
                    } catch (ProcessTimedOutException $exception) {
                        $this->trigger(
                            Event::ProcessTimeout,
                            new ProcessTimeoutEvent($process, $exception->getMessage())
                        );

                        try {
                            $this->processing->cancel($process);
                        } catch (ProcessingException $exception) {
                            $this->trigger(
                                Event::ProcessException,
                                new ProcessExceptionEvent($process, $exception)
                            );
                        }

                        $this->processPull($process);
                        unset($process);
                        continue;
                    }
                }

                $this->processRun($options);
            }
        );
        $this->keeperDisable();

        $this->onSignals($options->interruptSignals);
        if ($options->timeout > 0) {
            $this->onTimeout($options->timeout);
        }

        EventLoop::run();
    }

    /**
     * @param int[] $signals ext-pcntl
     * @throws UnsupportedFeatureException
     */
    private function onSignals(array $signals): void
    {
        foreach ($signals as $signal) {
            EventLoop::onSignal($signal, function () use ($signal): void {
                $this->loopExit($signal);
            });
        }
    }

    private function onTimeout(float $timeout): void
    {
        EventLoop::delay($timeout, function (): void {
            $this->trigger(
                Event::LoopTimeout,
                new LoopTimeoutEvent(new DateTimeImmutable())
            );
            $this->loopExit(SIGTERM);
        });
    }

    private function keeperDisable(): void
    {
        if ($this->keeperId === null) {
            throw new RuntimeException(
                'A Keeper identifier that can be used to cancel, enable or disable the callback.'
            );
        }

        EventLoop::disable($this->keeperId);
    }

    private function keeperEnable(): void
    {
        if ($this->keeperId === null) {
            throw new RuntimeException(
                'A Keeper identifier that can be used to cancel, enable or disable the callback.'
            );
        }

        EventLoop::enable($this->keeperId);
    }

    private function loopExit(int $signal): void
    {
        if ($this->runnerId === null || $this->keeperId === null) {
            throw new RuntimeException(
                'A Runner/Keeper identifier that can be used to cancel, enable or disable the callback.'
            );
        }

        EventLoop::cancel($this->runnerId);
        EventLoop::cancel($this->keeperId);

        foreach ($this->processesActive as $process) {
            if ($process->isRunning() || $process->isStarted()) {
                if ($process->stop(10., $signal) === null) {
                    continue;
                }

                $this->processing->pause($process);
                $this->trigger(Event::ProcessStop, new ProcessEvent($process));
            }
        }

        $this->processesActive = [];
        $this->processing->terminate($signal);

        exit($signal);
    }

    private function processRun(TaskManagerOptions $options): void
    {
        while (
            $this->processing->hasTaskProcess()
            && count($this->processesActive) < $options->getQueueSize()
        ) {
            $context = $this->processing->getTaskProcess();
            if ($context->timestamp > 0 && $context->timestamp > time()) {
                $this->processDelayPush($context);
                continue;
            }

            $this->processStart($context, $options);
        }
    }

    private function processStart(TaskProcessContext $context, TaskManagerOptions $options): void
    {
        if (array_key_exists($context->getHash(), $this->processesActive) === false) {
            $process = $this->processFactory->create($context, $options);
            $process->start();
            $this->processPush($process);
        }
    }

    private function processDelayPush(TaskProcessContext $context): void
    {
        if (
            array_key_exists($context->getHash(), $this->processesDelay)
            && $context->timestamp > $this->processesDelay[$context->getHash()]->timestamp
        ) {
            $this->processesDelay[$context->getHash()] = $context;
        }
    }

    private function processDelayActive(TaskProcessContext $context, TaskManagerOptions $options): void
    {
        if ($context->timestamp < time()) {
            $this->processStart($context, $options);
            unset($this->processesDelay[$context->getHash()]);
            $this->trigger(Event::ProcessDelay, new ProcessContextEvent($context));
        }
    }

    private function processPush(TaskProcess $process): void
    {
        if ($this->processesActive === []) {
            $this->isCheckForgotten = true;
            $this->keeperEnable();
        }

        $this->processesActive[$process->hash] = $process;
        $this->trigger(Event::ProcessPush, new ProcessEvent($process));
    }

    private function processPull(TaskProcess $process): void
    {
        $process->stop(0);
        unset(
            $this->processesActive[$process->hash],
            $this->processesDelay[$process->hash],
        );

        if ($this->processesActive === [] && $this->processesDelay === []) {
            $this->keeperDisable();
        }

        $this->trigger(Event::ProcessPull, new ProcessEvent($process));
    }
}

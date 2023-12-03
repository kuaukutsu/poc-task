<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use kuaukutsu\poc\task\exception\StateTransitionException;
use kuaukutsu\poc\task\service\action\TransitionState;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\EntityUuid;

final class TransitionStateTest extends TestCase
{
    use Container;

    private readonly TransitionState $transition;

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->transition = self::get(TransitionState::class);
    }

    public function testRunning(): void
    {
        $uuid = new EntityUuid();
        $flag = new TaskFlag();

        $this->transition->canAccessTransitionState(
            $uuid->getUuid(),
            $flag->unset()->setReady()->toValue(),
            $flag->unset()->setRunning()->toValue(),
        );

        $this->expectException(StateTransitionException::class);
        $this->transition->canAccessTransitionState(
            $uuid->getUuid(),
            $flag->unset()->setRunning()->toValue(),
            $flag->unset()->setRunning()->toValue(),
        );
    }

    public function testSuccess(): void
    {
        $uuid = new EntityUuid();
        $flag = new TaskFlag();

        $this->transition->canAccessTransitionState(
            $uuid->getUuid(),
            $flag->unset()->setRunning()->toValue(),
            $flag->unset()->setSuccess()->toValue(),
        );

        $this->expectException(StateTransitionException::class);
        $this->transition->canAccessTransitionState(
            $uuid->getUuid(),
            $flag->unset()->setRunning()->setError()->toValue(),
            $flag->unset()->setSuccess()->toValue(),
        );
    }

    public function testCancel(): void
    {
        $uuid = new EntityUuid();
        $flag = new TaskFlag();

        $this->transition->canAccessTransitionState(
            $uuid->getUuid(),
            $flag->unset()->setRunning()->toValue(),
            $flag->unset()->setCanceled()->toValue(),
        );

        $this->expectException(StateTransitionException::class);
        $this->transition->canAccessTransitionState(
            $uuid->getUuid(),
            $flag->unset()->setRunning()->setSuccess()->toValue(),
            $flag->unset()->setCanceled()->toValue(),
        );
    }

    public function testPause(): void
    {
        $uuid = new EntityUuid();
        $flag = new TaskFlag();

        $this->transition->canAccessTransitionState(
            $uuid->getUuid(),
            $flag->unset()->setRunning()->toValue(),
            $flag->unset()->setPaused()->toValue(),
        );

        $this->expectException(StateTransitionException::class);
        $this->transition->canAccessTransitionState(
            $uuid->getUuid(),
            $flag->unset()->setRunning()->setError()->toValue(),
            $flag->unset()->setPaused()->toValue(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Application;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class EventBusSpy implements MessageBusInterface
{
    /**
     * @param array<int, string> $spy
     */
    public function __construct(
        public array &$spy,
    ) {
    }

    /**
     * @param object                     $message
     * @param array<int, StampInterface> $stamps
     *
     * @return Envelope
     */
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $parts = explode('\\', $message::class);
        $this->spy[] = strtolower(end($parts));

        return new Envelope($message);
    }
}

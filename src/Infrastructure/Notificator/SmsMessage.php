<?php

declare(strict_types=1);

namespace Infrastructure\Notificator;

readonly class SmsMessage implements \Stringable
{
    public function __construct(
        public string $address,
        public string $body,
    ) {
    }

    public function __toString(): string
    {
        return $this->address.': '.$this->body;
    }
}

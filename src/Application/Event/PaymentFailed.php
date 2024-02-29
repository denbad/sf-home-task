<?php

declare(strict_types=1);

namespace Application\Event;

final readonly class PaymentFailed implements ApplicationEvent
{
    /**
     * @param string                        $url
     * @param array<int|string, int|string> $data
     * @param array<int|string, int|string> $errors
     */
    public function __construct(
        public string $url,
        public array $data,
        public array $errors,
    ) {
    }
}

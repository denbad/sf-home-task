<?php

declare(strict_types=1);

namespace Domain;

use Util\DecimalHelper;
use Webmozart\Assert\Assert;

final readonly class Amount
{
    private function __construct(
        private string $amount,
    ) {
    }

    public static function create(string $amount): self
    {
        self::assertPositiveAmount($amount);

        return new self($amount);
    }

    public static function createNegative(string $amount): self
    {
        self::assertNegativeAmount($amount);

        return new self($amount);
    }

    public static function zero(): self
    {
        return self::create(DecimalHelper::ZERO);
    }

    public function asString(): string
    {
        return $this->amount;
    }

    public function add(self $amount): self
    {
        $sum = DecimalHelper::add($this->asString(), $amount->asString());

        return new self($sum);
    }

    public function sub(self $amount): self
    {
        $sum = DecimalHelper::sub($this->asString(), $amount->asString());

        return new self($sum);
    }

    public function isNegative(): bool
    {
        return DecimalHelper::isNegative($this->amount);
    }

    public function isZero(): bool
    {
        return $this->equals(self::zero());
    }

    public function abs(): self
    {
        return new self(DecimalHelper::abs($this->asString()));
    }

    public function equals(self $amount): bool
    {
        return $this->asString() === $amount->asString();
    }

    private static function assertPositiveAmount(string $amount): void
    {
        Assert::numeric($amount);

        if (DecimalHelper::isNegative($amount)) {
            $message = sprintf('Amount value "%s" should not be negative.', $amount);

            throw new \InvalidArgumentException($message);
        }
    }

    private static function assertNegativeAmount(string $amount): void
    {
        Assert::numeric($amount);

        if (!DecimalHelper::isNegative($amount)) {
            $message = sprintf('Amount value "%s" should not be positive.', $amount);

            throw new \InvalidArgumentException($message);
        }
    }
}

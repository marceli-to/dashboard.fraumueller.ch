<?php

namespace App\Enums;

enum OrderStatus: string
{
    case OPEN = 'open';
    case FULFILLED = 'fulfilled';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'offen',
            self::FULFILLED => 'erledigt',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn($status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ])->toArray();
    }

    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
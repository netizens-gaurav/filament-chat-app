<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ConversationType: string implements HasColor, HasIcon, HasLabel
{
    case PRIVATE = 'private';
    case GROUP = 'group';

    public function getLabel(): string
    {
        return match ($this) {
            self::PRIVATE => 'Private',
            self::GROUP => 'Group',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PRIVATE => 'primary',
            self::GROUP => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PRIVATE => 'heroicon-s-user',
            self::GROUP => 'heroicon-s-users',
        };
    }

    public function getBasename(): string
    {
        return class_basename($this->value);
    }

    public static function fromBasename(string $basename): ?self
    {
        return collect(self::cases())->first(fn ($case) => class_basename($case->value) === $basename);
    }

    public static function getColumns(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($item) => [$item->value => $item->getLabel()])->all();
    }

    public static function getColumnColors(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($item) => [$item->value => $item->getColor()])->all();
    }
}

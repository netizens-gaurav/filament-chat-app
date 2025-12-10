<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MessageType: string implements HasColor, HasIcon, HasLabel
{
    case TEXT = 'text';
    case SYSTEM = 'system';

    public function getLabel(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::SYSTEM => 'System',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TEXT => 'primary',
            self::SYSTEM => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::TEXT => 'heroicon-s-chat-bubble-oval-left',
            self::SYSTEM => 'heroicon-s-information-circle',
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

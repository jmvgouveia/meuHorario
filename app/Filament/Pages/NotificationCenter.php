<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class NotificationCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.notification-center';
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function isTopNavigation(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return 'Notificações';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-bell';
    }

    public static function getNavigationSort(): ?int
    {
        return -1; // opcional: aparece à esquerda
    }
}

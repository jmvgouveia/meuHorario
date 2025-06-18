<?php

use App\Filament\Imports\Widgets;
use Filament\Widgets\Widget;


class NotificationCenter extends Widget
{
    protected static string $view = 'filament.widgets.notification-center';

    public function getNotifications()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        return $user ? $user->unreadNotifications : collect();
    }
}

<div class="relative">
    <button wire:click="$toggle('showDropdown')" class="relative">
        <x-heroicon-o-bell class="w-6 h-6" />
        @if ($unreadCount > 0)
        <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1">
            {{ $unreadCount }}
        </span>
        @endif
    </button>

    <div class="absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-lg z-50" x-show="showDropdown" @click.outside="showDropdown = false">
        <div class="p-2 text-sm font-bold border-b">Notificações</div>
        <ul class="max-h-60 overflow-y-auto">
            @foreach (auth()->user()->notifications->take(5) as $notification)
            <li class="p-2 border-b text-sm">
                {{ $notification->data['title'] ?? 'Notificação' }}
            </li>
            @endforeach
        </ul>
        <button wire:click="markAllAsRead" class="text-center text-xs text-blue-500 w-full py-2">Marcar todas como lidas</button>
    </div>
</div>
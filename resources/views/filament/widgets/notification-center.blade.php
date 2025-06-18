<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold mb-4">Notificações</h2>
        <ul>
            @forelse($this->getNotifications() as $notification)
            <li class="mb-2">
                <a href="{{ $notification->data['url'] ?? '#' }}" class="text-blue-500 hover:underline">
                    {{ $notification->data['titulo'] ?? 'Notificação' }}
                </a>
                <div class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
            </li>
            @empty
            <li>Sem notificações.</li>
            @endforelse
        </ul>
    </x-filament::card>
</x-filament::widget>
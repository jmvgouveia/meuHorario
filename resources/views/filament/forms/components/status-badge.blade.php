@php
$colors = [
'Aprovado' => 'bg-green-600',
'Pendente' => 'bg-yellow-500',
'Recusado' => 'bg-red-600',
'Escalado' => 'bg-blue-600',
'default' => 'bg-gray-500',
];
$colorClass = $colors[$status] ?? $colors['default'];
@endphp

<span class="inline-block px-3 py-1 text-xs font-semibold text-white rounded-full {{ $colorClass }}">
    {{ $status }}
</span>
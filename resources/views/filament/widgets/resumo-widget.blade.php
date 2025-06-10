<div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Resumo de Carga Horária</h3>

    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
        <li><strong>Horas Letivas :</strong> {{ $resumo['letiva'] ?? 0 }}</li>
        <li><strong>Horas Não Letivas :</strong> {{ $resumo['nao_letiva'] ?? 0 }}</li>
        <li><strong>Horas Letivas por Marcar:</strong> {{ $resumo['disponivel_letiva'] ?? 0 }}</li>
        <li><strong>Horas Não Letivas por Marcar:</strong> {{ $resumo['disponivel_naoletiva'] ?? 0 }}</li>
    </ul>
    <br>
    @if (!empty($resumo['cargos']))
    <div class="mt-4">
        <h4 class="font-semibold text-gray-800 dark:text-gray-100">Cargos com Redução:</h4>
        <ul class="list-disc list-inside mt-1 text-gray-600 dark:text-gray-300 text-sm">
            @foreach ($resumo['cargos'] as $cargo)
            <li>
                {{ $cargo['nome'] }} —
                Letiva: {{ $cargo['redução_letiva'] }},
                Não Letiva: {{ $cargo['redução_naoletiva'] }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@if ($resumo['reducoes'] ?? 0)
<div class="mt-4">
    <h4 class="font-semibold text-gray-800 dark:text-gray-100">Redução por Tempo de Serviço:</h4>
    <p class="text-sm text-gray-700 dark:text-gray-300">
        Total: {{ $resumo['reducoes'] }} horas
    </p>
</div>
@endif
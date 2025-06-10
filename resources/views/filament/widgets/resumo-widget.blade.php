<div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Resumo de Carga Horária</h3>

    <!-- <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
        <li><strong>Horas Letivas :</strong> {{ $resumo['letiva'] ?? 0 }}</li>
        <li><strong>Horas Não Letivas :</strong> {{ $resumo['nao_letiva'] ?? 0 }}</li>
        <li><strong>Horas Letivas por Marcar:</strong> {{ $resumo['disponivel_letiva'] ?? 0 }}</li>
        <li><strong>Horas Não Letivas por Marcar:</strong> {{ $resumo['disponivel_naoletiva'] ?? 0 }}</li>
    </ul> -->

    <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <h4 class="text-sm text-gray-500 dark:text-gray-400">Horas Letivas</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ $resumo['letiva'] ?? 0 }}
            </p>
        </div>

        <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <h4 class="text-sm text-gray-500 dark:text-gray-400">Horas Não Letivas</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ $resumo['nao_letiva'] ?? 0 }}
            </p>
        </div>

        <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <h4 class="text-sm text-gray-500 dark:text-gray-400">Letivas por Marcar</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ $resumo['disponivel_letiva'] ?? 0 }}
            </p>
        </div>

        <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <h4 class="text-sm text-gray-500 dark:text-gray-400">Não Letivas por Marcar</h4>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ $resumo['disponivel_naoletiva'] ?? 0 }}
            </p>
        </div>
    </div>



    <br>
    <div class="grid grid-cols-2 gap-4">

        @if (!empty($resumo['cargos']))
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Cargos com Redução</h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($resumo['cargos'] as $cargo)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-4">
                    <h5 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2">
                        {{ $cargo['nome'] }}
                    </h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        {{ $cargo['descricao'] }}
                    </p>
                    <div class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                        Letiva: {{ $cargo['redução_letiva'] }}h
                    </div>
                    <div class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                        Não Letiva: {{ $cargo['redução_naoletiva'] }}h
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <br>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if (!empty($resumo['tempo_reducoes']))
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Reduções por Tempo de Serviço</h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($resumo['tempo_reducoes'] as $reducao)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-4">
                    <h5 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-1">
                        {{ $reducao['nome'] }}
                    </h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        {{ $reducao['descricao'] }}
                    </p>
                    <div class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                        Letiva: {{ $reducao['valor'] }}h
                    </div>
                    <div class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                        Não Letiva: {{ $reducao['valor_nao_letivo'] }}h
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>
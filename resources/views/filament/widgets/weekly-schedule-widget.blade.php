<div class="w-full overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
    <table class="min-w-[800px] w-full table-fixed border-collapse text-center text-sm">
        <thead>
            <tr class="bg-gray-100 dark:bg-gray-800">
                <th class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 sticky left-0 z-10">
                    Horário
                </th>
                @foreach ($weekdays as $dayId => $dayName)
                <th class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                    {{ $dayName }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($timePeriods as $timePeriod)
            <tr>
                <td class="px-4 py-3 font-semibold bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 sticky left-0 z-10 whitespace-nowrap">
                    {{ $timePeriod->description }}
                </td>
                @foreach ($weekdays as $dayId => $dayName)
                @php
                $schedule = $calendar[$timePeriod->description][$dayName] ?? null;
                $bgColor = match($schedule->status ?? null) {
                'Aprovado' => 'bg-green-100 dark:bg-green-800',
                'Pendente' => 'bg-yellow-100 dark:bg-yellow-800',
                default => 'bg-white dark:bg-gray-900',
                };
                @endphp
                <td class="px-4 py-3 align-top {{ $bgColor }} text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-700">
                    @if ($schedule)
                    <a href="{{ route('filament.admin.resources.schedules.edit', $schedule->id) }}"
                        class="block p-2 rounded hover:bg-blue-100 dark:hover:bg-gray-800 transition">
                        <div class="text-sm font-semibold text-gray-800 dark:text-white">
                            {{ $schedule->subject->subject ?? 'Sem Matéria' }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            Sala: {{ $schedule->room->name }}
                        </div>
                        @if (!empty($schedule->classes))
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            @foreach ($schedule->classes as $class)
                            {{ $class->class ?? '-' }}@if (!$loop->last), @endif
                            @endforeach
                        </div>
                        @endif
                        @if (trim($schedule->turno ?? '') !== '')
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            Turno: {{ $schedule->turno }}
                        </div>
                        @endif
                    </a>
                    @else
                    <a href="{{ route('filament.admin.resources.schedules.create', [
                                    'weekday' => $dayId,
                                    'timeperiod' => $timePeriod->id
                                ]) }}"
                        class="block p-2 text-gray-400 dark:text-gray-600 hover:text-blue-600 dark:hover:text-blue-400 transition">
                        +
                    </a>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- <div class="w-full overflow-auto"> -->
<table class="min-w-full table-fixed border border-gray-300 dark:border-gray-600 text-center">
    <thead>
        <tr class="bg-gray-100 dark:bg-gray-800">
            <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                Horário
            </th>
            @foreach ($weekdays as $day)
            <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                {{ $day }}
            </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($timePeriods as $timePeriod)
        <tr>
            <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 font-semibold bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 sticky left-0 z-10 whitespace-nowrap">
                {{ $timePeriod->description }}
            </td>
            @foreach ($weekdays as $day)
            @php
            $schedule = $calendar[$timePeriod->description][$day] ?? null;
            @endphp
            <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 align-top bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                @if ($schedule)
                <div class="text-sm font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                    {{ $schedule->subject->subject ?? 'Sem Matéria' }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    Sala: {{ $schedule->room->name ?? '-' }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    Turno: {{ $schedule->turno ?? '-' }}
                </div>
                @else
                <span class="text-gray-300 dark:text-gray-700 text-sm">-</span>
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
<!-- </div> -->
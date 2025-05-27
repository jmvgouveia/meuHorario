 <!-- <div class="w-full overflow-auto">  -->
    <table class="min-w-full table-fixed border border-gray-300 text-center">
        <thead>
            <tr class="bg-gray-100">
<th class="border border-gray-300 px-4 py-2 bg-gray-200">Horário</th>
                @foreach ($weekdays as $day)
                    <th class="border border-gray-300 px-4 py-2 bg-gray-200 whitespace-nowrap">
                        {{ $day }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($timePeriods as $timePeriod)
                <tr>
                    <td class="border border-gray-300 px-4 py-3 font-semibold bg-gray-50 sticky left-0 z-10 whitespace-nowrap">
                        {{ $timePeriod->description }}
                    </td>
                    @foreach ($weekdays as $day)
                        @php
                            $schedule = $calendar[$timePeriod->description][$day] ?? null;
                        @endphp
                        <td class="border border-gray-300 px-4 py-3 align-top">
                            @if ($schedule)
                                <div class="text-sm font-medium text-gray-800 whitespace-nowrap">
                                    {{ $schedule->subject->subject ?? 'Sem Matéria' }}
                                    

                                </div>
                                <div class="text-xs text-gray-600">
                                    Sala: {{ $schedule->room->name ?? '-' }}
                                </div>
                                 <div class="text-xs text-gray-600">
                                    Turno: {{ $schedule->turno ?? '-' }}
                                </div>
                            @else
                                <span class="text-gray-300 text-sm">-</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
<!-- </div>  -->

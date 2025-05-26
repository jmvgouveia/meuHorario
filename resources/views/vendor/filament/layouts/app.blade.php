<body>
    {{ $slot }}

    @livewire('schedule-justification-modal')

    <script>
        window.addEventListener('open-justification-modal', event => {
            Livewire.emit('openJustificationModal', event.detail.conflictingScheduleId);
        });
    </script>
</body>
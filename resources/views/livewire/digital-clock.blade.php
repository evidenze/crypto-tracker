<div class="bg-white rounded-lg shadow-md p-4 mb-8 text-center"
     x-data="{ userTz: Intl.DateTimeFormat().resolvedOptions().timeZone }"
     x-init="
        setInterval(() => $wire.refreshTime(), 1000);
        $wire.setTimezone(userTz);
     ">
    <div class="text-3xl font-mono font-bold">
        {{ $time->format('H:i:s') }}
    </div>
    <div class="text-sm text-gray-500">
        {{ $time->format('l, F j, Y') }}
    </div>
</div>
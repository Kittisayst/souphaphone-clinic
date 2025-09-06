@php
    $record = $getRecord();
    $queueData = $record->queueServices()->get();
@endphp
<div>
    <ul>
        @foreach ($queueData as $q)
            <li>
                {{ $q->service->service_name }}
            </li>
        @endforeach
    </ul>
</div>
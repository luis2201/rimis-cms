<div class="timeline">
    @foreach($application->history->sortByDesc('created_at') as $entry)
        <div><i class="fas fa-history bg-info"></i><div class="timeline-item"><span class="time"><i class="far fa-clock"></i> {{ $entry->created_at->format('d/m/Y H:i') }}</span>
            <h3 class="timeline-header">{{ \App\Models\ResearcherApplication::STATUS_LABELS[$entry->new_status] ?? $entry->new_status }}</h3>
            @if($entry->comments && in_array($entry->new_status, ['draft','submitted','under_review','observed','approved','rejected','withdrawn']))<div class="timeline-body">{{ $entry->comments }}</div>@endif
        </div></div>
    @endforeach
    <div><i class="far fa-clock bg-gray"></i></div>
</div>

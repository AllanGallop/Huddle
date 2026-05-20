<x-mail::message>
# {{ __('Hello :name', ['name' => $user->name]) }}

{{ __('Here is what has been happening in :app since your last digest.', ['app' => config('app.name')]) }}

@if ($digest->newPublicEvents->isNotEmpty())
## {{ __('New public events') }}

@foreach ($digest->newPublicEvents as $event)
- **[{{ $event->name }}]({{ route('events.show', $event) }})** — {{ $event->start_time->format('j M Y, H:i') }} · {{ $event->location }}
@if ($event->volunteer_required)
  - {{ __('Volunteers needed') }}
@endif
@endforeach
@endif

@if ($digest->updatedVolunteerEvents->isNotEmpty())
## {{ __('Updates on events you follow') }}

@foreach ($digest->updatedVolunteerEvents as $event)
- **[{{ $event->name }}]({{ route('events.show', $event) }})** — {{ __('Updated') }} {{ $event->updated_at->format('j M Y') }} · {{ str($event->event_status)->headline() }}
@endforeach
@endif

@if ($digest->updatedVolunteerProjects->isNotEmpty())
## {{ __('Updates on projects you follow') }}

@foreach ($digest->updatedVolunteerProjects as $project)
- **[{{ $project->name }}]({{ route('projects.show', $project) }})** — {{ __('Updated') }} {{ $project->updated_at->format('j M Y') }} · {{ str($project->project_status)->headline() }}
@endforeach
@endif

@if ($digest->newVolunteerProjects->isNotEmpty())
## {{ __('Projects looking for volunteers') }}

@foreach ($digest->newVolunteerProjects as $project)
- **[{{ $project->name }}]({{ route('projects.show', $project) }})** — {{ __('Led by') }} {{ $project->leader->name }} · {{ str($project->project_status)->headline() }}
@endforeach
@endif

<x-mail::button :url="route('dashboard')">
{{ __('Open dashboard') }}
</x-mail::button>

{{ __('You are receiving this because you are a member of :app.', ['app' => config('app.name')]) }}

[{{ __('Unsubscribe from digests') }}]({{ $unsubscribeUrl }}) · [{{ __('Notification settings') }}]({{ route('notifications.edit') }})

{{ __('Thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>

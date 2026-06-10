<?php

namespace App\Services;

use App\Models\User;

class UserDataExportService
{
    public function export(User $user): array
    {
        $user->load([
            'role',
            'flags',
            'accreditationAssignments.accreditation',
            'membershipRenewalAssignments.membershipRenewal',
        ]);

        return [
            'exported_at' => now()->toIso8601String(),
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'role' => $user->role?->name,
                'digest_opt_out' => $user->digest_opt_out,
                'privacy_policy_accepted_at' => $user->privacy_policy_accepted_at?->toIso8601String(),
                'privacy_policy_version' => $user->privacy_policy_version,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
            'flags' => $user->flags->map(fn ($flag) => [
                'name' => $flag->name,
                'description' => $flag->description,
            ])->values()->all(),
            'accreditations' => $user->accreditationAssignments->map(fn ($assignment) => [
                'name' => $assignment->accreditation?->name,
                'is_active' => $assignment->is_active,
            ])->values()->all(),
            'membership_periods' => $user->membershipRenewalAssignments->map(fn ($assignment) => [
                'period' => $assignment->membershipRenewal?->name,
                'start_date' => $assignment->membershipRenewal?->start_date?->toDateString(),
                'end_date' => $assignment->membershipRenewal?->end_date?->toDateString(),
            ])->values()->all(),
            'project_comments' => $user->projectComments()
                ->with('project:id,name')
                ->get()
                ->map(fn ($comment) => [
                    'project' => $comment->project?->name,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at?->toIso8601String(),
                ])->values()->all(),
            'event_comments' => $user->eventComments()
                ->with('event:id,name')
                ->get()
                ->map(fn ($comment) => [
                    'event' => $comment->event?->name,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at?->toIso8601String(),
                ])->values()->all(),
            'projects_led' => $user->ledProjects()
                ->get(['id', 'name', 'project_status', 'created_at'])
                ->map(fn ($project) => [
                    'name' => $project->name,
                    'status' => $project->project_status,
                    'created_at' => $project->created_at?->toIso8601String(),
                ])->values()->all(),
            'projects_created' => $user->createdProjects()
                ->get(['id', 'name', 'project_status', 'created_at'])
                ->map(fn ($project) => [
                    'name' => $project->name,
                    'status' => $project->project_status,
                    'created_at' => $project->created_at?->toIso8601String(),
                ])->values()->all(),
            'project_volunteers' => $user->projectVolunteers()
                ->with('project:id,name')
                ->get()
                ->map(fn ($volunteer) => [
                    'project' => $volunteer->project?->name,
                    'created_at' => $volunteer->created_at?->toIso8601String(),
                ])->values()->all(),
            'event_volunteers' => $user->eventVolunteers()
                ->with('event:id,name')
                ->get()
                ->map(fn ($volunteer) => [
                    'event' => $volunteer->event?->name,
                    'created_at' => $volunteer->created_at?->toIso8601String(),
                ])->values()->all(),
            'events_created' => $user->createdEvents()
                ->get(['id', 'name', 'event_status', 'created_at'])
                ->map(fn ($event) => [
                    'name' => $event->name,
                    'status' => $event->event_status,
                    'created_at' => $event->created_at?->toIso8601String(),
                ])->values()->all(),
            'form_submissions' => $user->formSubmissions()
                ->with(['form:id,title', 'answers.question:id,body'])
                ->get()
                ->map(fn ($submission) => [
                    'form' => $submission->form?->title,
                    'submitted_at' => $submission->submitted_at?->toIso8601String(),
                    'score' => $submission->score,
                    'max_score' => $submission->max_score,
                    'passed' => $submission->passed,
                    'answers' => $submission->answers->map(fn ($answer) => [
                        'question' => $answer->question?->body,
                        'value' => $answer->value,
                    ])->values()->all(),
                ])->values()->all(),
            'wiki_versions_authored' => $user->wikiPageVersions()
                ->with('page:id,slug')
                ->get()
                ->map(fn ($version) => [
                    'page_slug' => $version->page?->slug,
                    'title' => $version->title,
                    'change_summary' => $version->change_summary,
                    'created_at' => $version->created_at?->toIso8601String(),
                ])->values()->all(),
        ];
    }

    public function filename(User $user): string
    {
        $slug = str($user->name)->slug()->toString() ?: 'user';

        return 'huddle-data-export-'.$slug.'-'.now()->format('Y-m-d').'.json';
    }
}

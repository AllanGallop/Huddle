<?php

namespace App\Data;

use Illuminate\Support\Collection;

class CommunityDigest
{
    public function __construct(
        public Collection $newPublicEvents,
        public Collection $updatedVolunteerEvents,
        public Collection $updatedVolunteerProjects,
        public Collection $newVolunteerProjects,
    ) {}

    public function hasContent(): bool
    {
        return $this->newPublicEvents->isNotEmpty()
            || $this->updatedVolunteerEvents->isNotEmpty()
            || $this->updatedVolunteerProjects->isNotEmpty()
            || $this->newVolunteerProjects->isNotEmpty();
    }
}

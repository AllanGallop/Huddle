<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectImageController extends Controller
{
    public function __invoke(Request $request, Project $project, ProjectImage $projectImage): StreamedResponse
    {
        $this->authorize('view', $project);

        abort_unless($projectImage->project_id === $project->id, 404);

        $disk = $projectImage->storageDisk();

        abort_unless(Storage::disk($disk)->exists($projectImage->image_url), 404);

        $mimeType = Storage::disk($disk)->mimeType($projectImage->image_url) ?: 'image/jpeg';

        return Storage::disk($disk)->response(
            $projectImage->image_url,
            basename($projectImage->image_url),
            ['Content-Type' => $mimeType],
            'inline',
        );
    }
}

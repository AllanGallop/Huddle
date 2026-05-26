<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;

class WikiImageUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canManageWiki(), 403);

        $request->validate([
            'file' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])->max(10240)],
            'image' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])->max(10240)],
        ]);

        $uploadedFile = $request->file('file') ?? $request->file('image');

        if (! $uploadedFile) {
            throw ValidationException::withMessages([
                'file' => __('Please choose a file to upload.'),
            ]);
        }

        $path = $uploadedFile->store('wiki/'.now()->format('Y/m'), 'public');
        $url = route('wiki.asset', ['path' => $path]);

        return response()->json([
            'url' => $url,
            'markdown' => str_starts_with((string) $uploadedFile->getMimeType(), 'image/')
                ? '![]('.$url.')'
                : '['.$uploadedFile->getClientOriginalName().']('.$url.')',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class WikiImageUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canManageWiki(), 403);

        $validated = $request->validate([
            'image' => ['required', File::image()->max(5120)],
        ]);

        $path = $validated['image']->store('wiki/'.now()->format('Y/m'), 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
            'markdown' => '![]('.Storage::disk('public')->url($path).')',
        ]);
    }
}

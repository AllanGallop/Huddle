<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WikiAssetController extends Controller
{
    public function __invoke(Request $request, string $path): StreamedResponse
    {
        abort_unless($request->user(), 403);

        $path = trim($path, '/');

        abort_unless(str_starts_with($path, 'wiki/'), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return Storage::disk('public')->response(
            $path,
            basename($path),
            ['Content-Type' => $mimeType],
            'inline',
        );
    }
}

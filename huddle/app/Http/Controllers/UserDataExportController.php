<?php

namespace App\Http\Controllers;

use App\Services\UserDataExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserDataExportController extends Controller
{
    public function __invoke(Request $request, UserDataExportService $exports): StreamedResponse
    {
        $user = $request->user();

        $payload = json_encode(
            $exports->export($user),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        return response()->streamDownload(
            static function () use ($payload): void {
                echo $payload;
            },
            $exports->filename($user),
            ['Content-Type' => 'application/json'],
        );
    }
}

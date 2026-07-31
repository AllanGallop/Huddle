<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Throwable;

class ApplicationUpdateService
{
    /**
     * Installed version from the release VERSION file, or null if unknown.
     */
    public function installedVersion(): ?string
    {
        $path = base_path('VERSION');

        if (! is_file($path)) {
            return null;
        }

        $version = trim((string) file_get_contents($path));

        return $version !== '' ? $version : null;
    }

    /**
     * @return array{
     *     ok: bool,
     *     tag: ?string,
     *     name: ?string,
     *     zip_url: ?string,
     *     html_url: ?string,
     *     message: ?string,
     *     update_available: bool
     * }
     */
    public function checkLatestRelease(): array
    {
        $repo = (string) config('huddle.github_repo', 'AllanGallop/Huddle');
        $url = "https://api.github.com/repos/{$repo}/releases/latest";

        try {
            $response = Http::accept('application/vnd.github+json')
                ->withHeaders([
                    'X-GitHub-Api-Version' => '2022-11-28',
                    'User-Agent' => 'Huddle-ApplicationUpdate',
                ])
                ->timeout(15)
                ->get($url);

            if (! $response->successful()) {
                return $this->releaseResult(
                    ok: false,
                    message: __('Could not reach GitHub for release information (HTTP :status).', [
                        'status' => $response->status(),
                    ]),
                );
            }

            $payload = $response->json();
            $tag = is_array($payload) ? ($payload['tag_name'] ?? null) : null;
            $htmlUrl = is_array($payload) ? ($payload['html_url'] ?? null) : null;
            $assets = is_array($payload) ? ($payload['assets'] ?? []) : [];

            $zipUrl = null;
            $zipName = null;
            foreach ($assets as $asset) {
                if (! is_array($asset)) {
                    continue;
                }
                $name = (string) ($asset['name'] ?? '');
                if (preg_match('/^huddle-.*\.zip$/', $name) === 1) {
                    $zipUrl = $asset['browser_download_url'] ?? null;
                    $zipName = $name;
                    break;
                }
            }

            if (! is_string($tag) || $tag === '') {
                return $this->releaseResult(
                    ok: false,
                    message: __('No GitHub release was found for this repository.'),
                );
            }

            $installed = $this->installedVersion();
            $updateAvailable = $this->isNewerThanInstalled($tag, $installed);

            return $this->releaseResult(
                ok: true,
                tag: $tag,
                name: is_string($zipName) ? $zipName : null,
                zipUrl: is_string($zipUrl) ? $zipUrl : null,
                htmlUrl: is_string($htmlUrl) ? $htmlUrl : null,
                updateAvailable: $updateAvailable,
            );
        } catch (Throwable $exception) {
            return $this->releaseResult(
                ok: false,
                message: __('Could not check for updates: :error', [
                    'error' => $exception->getMessage(),
                ]),
            );
        }
    }

    /**
     * Run migrations, seeders, and rebuild caches (same as scripts/migrate.sh).
     *
     * @return array{ok: bool, message: string, output: list<string>}
     */
    public function applyDatabaseUpdates(): array
    {
        $output = [];

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output[] = 'Database migrations completed.';
            $output = array_merge($output, $this->artisanLines(Artisan::output()));

            Artisan::call('db:seed', ['--force' => true]);
            $output[] = 'Database seeders completed.';
            $output = array_merge($output, $this->artisanLines(Artisan::output()));

            try {
                // Clear compiled views; avoid config:cache/optimize:clear mid-request on shared hosts.
                Artisan::call('view:clear');
                $output[] = 'Compiled views cleared.';
            } catch (Throwable) {
                $output[] = 'Skipped clearing compiled views.';
            }

            return [
                'ok' => true,
                'message' => __('Database update completed.'),
                'output' => $output,
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'message' => $exception->getMessage(),
                'output' => $output,
            ];
        }
    }

    public function isNewerThanInstalled(string $latestTag, ?string $installed): bool
    {
        if ($installed === null || $installed === '') {
            return true;
        }

        $latest = ltrim(trim($latestTag), 'vV');
        $current = ltrim(trim($installed), 'vV');

        if ($latest === $current) {
            return false;
        }

        if (
            preg_match('/^\d+(\.\d+){0,3}/', $latest) === 1
            && preg_match('/^\d+(\.\d+){0,3}/', $current) === 1
        ) {
            return version_compare($latest, $current, '>');
        }

        return $latest !== $current;
    }

    /**
     * @return array{
     *     ok: bool,
     *     tag: ?string,
     *     name: ?string,
     *     zip_url: ?string,
     *     html_url: ?string,
     *     message: ?string,
     *     update_available: bool
     * }
     */
    protected function releaseResult(
        bool $ok,
        ?string $tag = null,
        ?string $name = null,
        ?string $zipUrl = null,
        ?string $htmlUrl = null,
        ?string $message = null,
        bool $updateAvailable = false,
    ): array {
        return [
            'ok' => $ok,
            'tag' => $tag,
            'name' => $name,
            'zip_url' => $zipUrl,
            'html_url' => $htmlUrl,
            'message' => $message,
            'update_available' => $updateAvailable,
        ];
    }

    /**
     * @return list<string>
     */
    protected function artisanLines(string $output): array
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($output)) ?: [];

        return array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));
    }
}

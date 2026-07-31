#Requires -Version 5.1
[CmdletBinding()]
param(
    [switch]$NoSeed,
    [switch]$NoCache,
    [switch]$Help
)

$ErrorActionPreference = "Stop"

function Show-Usage {
    @"
Usage: migrate.ps1 [options]

Run database migrations and seeders for a Huddle installation.

Options:
  -NoSeed    Skip db:seed
  -NoCache   Skip config/route/view cache rebuild
  -Help      Show this help
"@
}

if ($Help) {
    Show-Usage
    exit 0
}

$ScriptDir = $PSScriptRoot

function Resolve-AppRoot {
    $repoHuddle = Join-Path $ScriptDir "..\huddle\artisan"
    if (Test-Path $repoHuddle) {
        return (Resolve-Path (Join-Path $ScriptDir "..\huddle")).Path
    }
    $inScripts = Join-Path $ScriptDir "artisan"
    if (Test-Path $inScripts) {
        return $ScriptDir
    }
    $parent = Join-Path $ScriptDir "..\artisan"
    if (Test-Path $parent) {
        return (Resolve-Path (Join-Path $ScriptDir "..")).Path
    }
    $cwd = Join-Path (Get-Location) "artisan"
    if (Test-Path $cwd) {
        return (Get-Location).Path
    }
    return $null
}

$AppRoot = Resolve-AppRoot
if (-not $AppRoot) {
    Write-Error "Could not find Laravel app root (artisan). Run from the app directory or keep this script under scripts/."
    exit 1
}

$autoload = Join-Path $AppRoot "vendor\autoload.php"
if (-not (Test-Path $autoload)) {
    Write-Error "Missing vendor/autoload.php in $AppRoot - upload a full release package first."
    exit 1
}

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Error "php is required on PATH."
    exit 1
}

Set-Location $AppRoot
Write-Host "App root: $AppRoot"
Write-Host "Running migrations..."
& php artisan migrate --force
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

if (-not $NoSeed) {
    Write-Host "Running seeders..."
    & php artisan db:seed --force
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
} else {
    Write-Host "Skipping seeders (-NoSeed)."
}

if (-not $NoCache) {
    Write-Host "Rebuilding caches..."
    & php artisan config:cache
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    & php artisan route:cache
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    & php artisan view:cache
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
} else {
    Write-Host "Skipping cache rebuild (-NoCache)."
}

Write-Host ""
Write-Host "Done. Restart queue workers if they are running."
Write-Host ""

#Requires -Version 5.1
[CmdletBinding()]
param(
    [string]$Repo = "AllanGallop/Huddle",
    [string]$Tag = "",
    [string]$Output = "",
    [switch]$Help
)

$ErrorActionPreference = "Stop"

$Root = Resolve-Path (Join-Path $PSScriptRoot "..")
$ApiBase = "https://api.github.com"

function Show-Usage {
    @"
Usage: download-latest-release.ps1 [options]

Download the latest (or a specific) Huddle release zip from GitHub.

Options:
  -Repo owner/name   GitHub repository (default: AllanGallop/Huddle)
  -Tag vX.Y.Z        Specific release tag instead of latest
  -Output DIR        Download directory (default: build/download/)
  -Help              Show this help
"@
}

if ($Help) {
    Show-Usage
    exit 0
}

if (-not $Output) {
    $Output = Join-Path $Root "build\download"
}

if ($Tag) {
    $ApiUrl = "$ApiBase/repos/$Repo/releases/tags/$Tag"
} else {
    $ApiUrl = "$ApiBase/repos/$Repo/releases/latest"
}

Write-Host "Fetching release info from $ApiUrl ..."

try {
    $headers = @{
        "Accept"               = "application/vnd.github+json"
        "X-GitHub-Api-Version" = "2022-11-28"
        "User-Agent"           = "Huddle-download-latest-release"
    }
    $release = Invoke-RestMethod -Uri $ApiUrl -Headers $headers
} catch {
    Write-Error "Failed to fetch release info. Check the repo name and that a release exists. $_"
    exit 1
}

$asset = $release.assets | Where-Object { $_.name -match '^huddle-.*\.zip$' } | Select-Object -First 1
if (-not $asset) {
    Write-Error "No huddle-*.zip asset found on release $($release.tag_name)."
    exit 1
}

New-Item -ItemType Directory -Force -Path $Output | Out-Null
$dest = Join-Path $Output $asset.name

Write-Host "Downloading $($asset.name) (tag: $($release.tag_name)) ..."
Invoke-WebRequest -Uri $asset.browser_download_url -OutFile $dest -Headers @{
    "Accept"     = "application/octet-stream"
    "User-Agent" = "Huddle-download-latest-release"
}

Write-Host ""
Write-Host "Saved: $dest"
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. Extract the zip locally."
Write-Host "  2. Upload the package contents via FTP/SFTP."
Write-Host "  3. Do NOT overwrite .env, storage/, or existing database/*.sqlite files."
Write-Host "  4. On the server, run: .\scripts\migrate.ps1"
Write-Host ""

$ErrorActionPreference = "Stop"

$Root = Resolve-Path (Join-Path $PSScriptRoot "..")
$Huddle = Join-Path $Root "huddle"

Write-Host "Resetting install artifacts on host..."
Remove-Item (Join-Path $Huddle ".env") -Force -ErrorAction SilentlyContinue
Remove-Item (Join-Path $Huddle "database\database.sqlite") -Force -ErrorAction SilentlyContinue
Copy-Item (Join-Path $Huddle "public\.htaccess.setup") (Join-Path $Huddle "public\.htaccess") -Force

Set-Location $Root
docker compose -f DockerCompose.yaml down -v
Write-Host ""
Write-Host "Open http://localhost:8000 — MySQL host is 'db'"
Write-Host ""
docker compose -f DockerCompose.yaml up --build

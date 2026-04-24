# Jalankan script ini tiap mau demo: .\tunnel.ps1

$ErrorActionPreference = "Stop"
$AppDir = $PSScriptRoot

# Hapus file hot agar Laravel tidak pakai Vite dev server
$hotFile = Join-Path $PSScriptRoot "public\hot"
if (Test-Path $hotFile) {
    Remove-Item $hotFile -Force
    Write-Host "Vite dev server dinonaktifkan (file hot dihapus)." -ForegroundColor Yellow
}

# Build asset untuk production
Write-Host "Building assets..." -ForegroundColor Cyan
Set-Location $PSScriptRoot
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "Build gagal. Periksa error di atas." -ForegroundColor Red
    exit 1
}
Write-Host "Build selesai." -ForegroundColor Green
Write-Host ""

# Simpan nilai asli sebelum diubah
$envPath = Join-Path $AppDir ".env"
$envContent = Get-Content $envPath -Raw
$originalAppUrl     = if ($envContent -match "(?m)^APP_URL=(.+)")     { $Matches[1].Trim() } else { "http://school-management.test" }
$originalSessionDomain = if ($envContent -match "(?m)^SESSION_DOMAIN=(.*)") { $Matches[1].Trim() } else { "" }
Write-Host "APP_URL asli: $originalAppUrl" -ForegroundColor Gray

# Fungsi restore — dipanggil saat selesai/Ctrl+C
function Restore-Env {
    Write-Host ""
    Write-Host "Mengembalikan konfigurasi lokal..." -ForegroundColor Yellow
    $current = Get-Content $envPath -Raw
    $current = $current -replace "(?m)^APP_URL=.*", "APP_URL=$originalAppUrl"
    $current = $current -replace "(?m)^ASSET_URL=.*\r?\n", ""
    $current = $current -replace "(?m)^SESSION_DOMAIN=.*", "SESSION_DOMAIN=$originalSessionDomain"
    Set-Content $envPath $current -NoNewline
    Set-Location $AppDir
    php artisan config:clear | Out-Null
    php artisan route:clear  | Out-Null
    php artisan view:clear   | Out-Null
    Write-Host "APP_URL dikembalikan ke $originalAppUrl" -ForegroundColor Green
    Write-Host "Cache dibersihkan. Local siap dipakai lagi." -ForegroundColor Green
}

Write-Host "Memulai Cloudflare Tunnel..." -ForegroundColor Cyan

$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName               = "C:\Program Files (x86)\cloudflared\cloudflared.exe"
$psi.Arguments              = "tunnel --url http://localhost:80 --http-host-header school-management.test"
$psi.RedirectStandardOutput = $false
$psi.RedirectStandardError  = $true
$psi.UseShellExecute        = $false
$psi.CreateNoWindow         = $false

$process = [System.Diagnostics.Process]::Start($psi)

$tunnelUrl = $null
$timeout   = [System.DateTime]::Now.AddSeconds(30)

Write-Host "Menunggu URL tunnel..." -ForegroundColor Yellow

while ([System.DateTime]::Now -lt $timeout) {
    $line = $process.StandardError.ReadLine()
    if ($line -match "https://[a-z0-9\-]+\.trycloudflare\.com") {
        $tunnelUrl = $Matches[0]
        break
    }
}

if (-not $tunnelUrl) {
    Write-Host "Gagal mendapatkan URL tunnel." -ForegroundColor Red
    $process.Kill()
    exit 1
}

Write-Host ""
Write-Host "URL Tunnel ditemukan: $tunnelUrl" -ForegroundColor Green

# Update .env — APP_URL + ASSET_URL ke tunnel URL (supaya asset load via HTTPS)
$envContent = Get-Content $envPath -Raw
$envContent = $envContent -replace "(?m)^APP_URL=.*", "APP_URL=$tunnelUrl"
if ($envContent -match "(?m)^ASSET_URL=") {
    $envContent = $envContent -replace "(?m)^ASSET_URL=.*", "ASSET_URL=$tunnelUrl"
} else {
    $envContent = $envContent -replace "(?m)^APP_URL=.*", "APP_URL=$tunnelUrl`nASSET_URL=$tunnelUrl"
}
# Kosongkan SESSION_DOMAIN supaya cookie jalan di tunnel domain
$envContent = $envContent -replace "(?m)^SESSION_DOMAIN=.*", "SESSION_DOMAIN="
Set-Content $envPath $envContent -NoNewline
Write-Host "APP_URL + ASSET_URL + SESSION_DOMAIN diupdate" -ForegroundColor Green

# Rebuild cache Laravel
Set-Location $AppDir
php artisan config:cache | Out-Null
php artisan route:cache  | Out-Null
php artisan view:cache   | Out-Null
Write-Host "Cache Laravel di-rebuild." -ForegroundColor Green

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " SIAP! Link bisa di-share:" -ForegroundColor Cyan
Write-Host " $tunnelUrl" -ForegroundColor White
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Tekan Ctrl+C untuk stop tunnel." -ForegroundColor Gray
Write-Host "APP_URL lokal akan dikembalikan otomatis." -ForegroundColor Gray

# Tunggu tunnel, restore otomatis saat keluar (termasuk Ctrl+C)
try {
    $process.StandardError.ReadToEnd() | Write-Host
    $process.WaitForExit()
} finally {
    if (-not $process.HasExited) { $process.Kill() }
    Restore-Env
}

$ErrorActionPreference = "Stop"

$ProjectRoot = $PSScriptRoot
$PrereqDir = Join-Path $ProjectRoot "installer\prerequisites"

if (-not (Test-Path $PrereqDir)) {
    New-Item -ItemType Directory -Path $PrereqDir -Force | Out-Null
}

function Download-File {
    param([string]$Url, [string]$Dest)
    if (-not (Test-Path $Dest)) {
        Write-Host "Downloading $(Split-Path $Dest -Leaf)..." -ForegroundColor Cyan
        Invoke-WebRequest -Uri $Url -OutFile $Dest -UseBasicParsing -TimeoutSec 300
    } else {
        Write-Host "File $(Split-Path $Dest -Leaf) already exists." -ForegroundColor Green
    }
}

# 1. Download Prerequisites
$PhpUrl = "https://windows.php.net/downloads/releases/archives/php-8.2.12-Win32-vs16-x64.zip"
$MariadbUrl = "https://archive.mariadb.org/mariadb-11.2.2/winx64-packages/mariadb-11.2.2-winx64.zip"
$ComposerUrl = "https://getcomposer.org/download/latest-stable/composer.phar"
$CloudflaredUrl = "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"

Download-File $PhpUrl (Join-Path $PrereqDir "php.zip")
Download-File $MariadbUrl (Join-Path $PrereqDir "mariadb.zip")
Download-File $ComposerUrl (Join-Path $PrereqDir "composer.phar")
Download-File $CloudflaredUrl (Join-Path $PrereqDir "cloudflared-windows-amd64.exe")

# 2. Check for Inno Setup
$ISCC = "$env:ProgramFiles (x86)\Inno Setup 6\ISCC.exe"
if (-not (Test-Path $ISCC)) {
    $ISCC = "$env:ProgramFiles\Inno Setup 6\ISCC.exe"
}

if (-not (Test-Path $ISCC)) {
    Write-Host "Inno Setup is not installed. Installing via winget..." -ForegroundColor Yellow
    & winget install -e --id JRSoftware.InnoSetup --accept-source-agreements --accept-package-agreements --silent
    $ISCC = "$env:ProgramFiles (x86)\Inno Setup 6\ISCC.exe"
    if (-not (Test-Path $ISCC)) {
        $ISCC = "$env:ProgramFiles\Inno Setup 6\ISCC.exe"
    }
    if (-not (Test-Path $ISCC)) {
        Write-Host "Error: Could not find Inno Setup compiler (ISCC.exe) after installation." -ForegroundColor Red
        exit 1
    }
}

# 3. Build the Installer
$IssFile = Join-Path $ProjectRoot "installer\setup.iss"
if (-not (Test-Path $IssFile)) {
    Write-Host "Error: setup.iss not found!" -ForegroundColor Red
    exit 1
}

Write-Host "Building EXE with Inno Setup..." -ForegroundColor Cyan
& $ISCC $IssFile

Write-Host "Build Complete! Check the output directory." -ForegroundColor Green

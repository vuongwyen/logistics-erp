# ============================================================
#  NT LOGISTICS ERP -- All-in-One Installer
#  install.ps1  |  Version 3.0 (Portable x86/x64)
#  Yeu cau: Windows 10/11, Quyen Administrator
# ============================================================

$ErrorActionPreference = "Stop"

# â”€â”€ Yeu cau quyen Administrator â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$IsAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]"Administrator")
if (-not $IsAdmin) {
    Write-Host "  Dang yeu cau quyen Administrator..." -ForegroundColor Yellow
    $ScriptPath = if ($PSCommandPath) { $PSCommandPath } else { $MyInvocation.MyCommand.Path }
    Start-Process PowerShell -Verb RunAs "-NoProfile -ExecutionPolicy Bypass -File `"$ScriptPath`""
    exit
}

$Host.UI.RawUI.WindowTitle = "NT Logistics ERP -- Bo Cai Dat Portable (Administrator)"

function Write-Banner {
    Clear-Host
    Write-Host ""
    Write-Host "  +======================================================+" -ForegroundColor Cyan
    Write-Host "  |                                                      |" -ForegroundColor Cyan
    Write-Host "  |    NT LOGISTICS ERP -- Bo Cai Dat Tu Dong            |" -ForegroundColor Cyan
    Write-Host "  |         Phan mem Quan ly Van tai Noi Dia              |" -ForegroundColor Cyan
    Write-Host "  |             v3.0.0 [PORTABLE SERVER]                 |" -ForegroundColor Cyan
    Write-Host "  |                                                      |" -ForegroundColor Cyan
    Write-Host "  +======================================================+" -ForegroundColor Cyan
    Write-Host ""
}

function Write-Step   { param([int]$N, [string]$T, [string]$M) Write-Host ""; Write-Host "  [$N/$T] $M" -ForegroundColor Yellow }
function Write-OK     { param([string]$M) Write-Host "     [OK] $M" -ForegroundColor Green }
function Write-WARN   { param([string]$M) Write-Host "     [!!] $M" -ForegroundColor Yellow }
function Write-ERR    { param([string]$M) Write-Host "     [XX] $M" -ForegroundColor Red }
function Write-INFO   { param([string]$M) Write-Host "     [..] $M" -ForegroundColor Cyan }
function Write-Divider { Write-Host "  ------------------------------------------------------" -ForegroundColor DarkGray }

function Set-EnvValue {
    param([string]$Content, [string]$Key, [string]$Value)
    if ($Content -match "(?m)^${Key}=") { return $Content -replace "(?m)^${Key}=.*$", "${Key}=${Value}" }
    return $Content + "`n${Key}=${Value}"
}

$ProjectRoot = Split-Path $PSScriptRoot -Parent
if (-not $ProjectRoot) { $ProjectRoot = Split-Path (Get-Location).Path -Parent }

Write-Banner

$PrereqDir = Join-Path $PSScriptRoot "prerequisites"
$ServerDir = Join-Path $ProjectRoot "server"
$PhpDir = Join-Path $ServerDir "php"
$MysqlDir = Join-Path $ServerDir "mysql"
$PhpBin = Join-Path $PhpDir "php.exe"
$MysqldBin = Join-Path $MysqlDir "bin\mysqld.exe"
$MysqlBin = Join-Path $MysqlDir "bin\mysql.exe"
$MysqlBin = Join-Path $MysqlDir "bin\mysql.exe"
$ComposerPhar = Join-Path $ServerDir "composer.phar"

if (-not (Test-Path $ServerDir)) { New-Item -ItemType Directory -Path $ServerDir | Out-Null }

# â”€â”€ Buoc 1: Bung nen Portable Server â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Write-Step 1 9 "Cai dat may chu Portable (PHP & MariaDB)..."

if (-not (Test-Path $PhpBin)) {
    $PhpZip = Join-Path $PrereqDir "php.zip"
    if (Test-Path $PhpZip) {
        Write-INFO "Dang bung nen PHP..."
        Expand-Archive -Path $PhpZip -DestinationPath $PhpDir -Force
        Write-OK "Da cai dat PHP."
    } else {
        Write-ERR "Khong tim thay php.zip trong thu muc prerequisites!"
        Read-Host "Nhan Enter de thoat"
        exit 1
    }
} else { Write-OK "PHP da duoc cai dat." }

if (-not (Test-Path $MysqldBin)) {
    $MariadbZip = Join-Path $PrereqDir "mariadb.zip"
    if (Test-Path $MariadbZip) {
        Write-INFO "Dang bung nen MariaDB (co the mat 1-2 phut)..."
        # MariaDB zip co the chua thu muc goc mariadb-10.x.x-win32. Ta giai nen vao temp roi move.
        $TempDb = Join-Path $ServerDir "temp_mariadb"
        Expand-Archive -Path $MariadbZip -DestinationPath $TempDb -Force
        $InnerDir = Get-ChildItem -Path $TempDb -Directory | Select-Object -First 1
        if (Test-Path $MysqlDir) { Remove-Item -Path $MysqlDir -Recurse -Force }
        Rename-Item -Path $InnerDir.FullName -NewName "mysql"
        Move-Item -Path (Join-Path $TempDb "mysql") -Destination $ServerDir -Force
        Remove-Item $TempDb -Recurse -Force
        Write-OK "Da cai dat MariaDB."
    } else {
        Write-ERR "Khong tim thay mariadb.zip trong thu muc prerequisites!"
        Read-Host "Nhan Enter de thoat"
        exit 1
    }
} else { Write-OK "MariaDB da duoc cai dat." }

if (-not (Test-Path $ComposerPhar)) {
    $ComposerSrc = Join-Path $PrereqDir "composer.phar"
    if (Test-Path $ComposerSrc) {
        Copy-Item $ComposerSrc $ComposerPhar -Force
        Write-OK "Da copy Composer."
    } else {
        Write-ERR "Khong tim thay composer.phar!"
        Read-Host "Nhan Enter de thoat"
        exit 1
    }
}

# â”€â”€ Buoc 2: Cau hinh PHP â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Write-Step 2 9 "Cau hinh PHP..."
$PhpIni = Join-Path $PhpDir "php.ini"
if (-not (Test-Path $PhpIni)) {
    $PhpIniDev = Join-Path $PhpDir "php.ini-development"
    Copy-Item $PhpIniDev $PhpIni -Force
    $IniContent = Get-Content $PhpIni -Raw
    # Cau hinh extension dir
    $IniContent = $IniContent -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
    # Bat cac extension can thiet
    $Exts = @("curl", "fileinfo", "mbstring", "openssl", "pdo_mysql", "zip")
    foreach ($ext in $Exts) {
        $IniContent = $IniContent -replace ";extension=$ext", "extension=$ext"
    }
    Set-Content $PhpIni -Value $IniContent -Encoding ASCII
    Write-OK "Da cau hinh php.ini thanh cong."
} else {
    Write-OK "PHP da duoc cau hinh tu truoc."
}

# â”€â”€ Buoc 3: Cau hinh MariaDB Service â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Write-Step 3 9 "Cau hinh MariaDB Service..."
$DbDataDir = Join-Path $MysqlDir "data"
if (-not (Test-Path $DbDataDir)) {
    Write-INFO "Khoi tao Data MariaDB/MySQL..."
    $InstallDb1 = Join-Path $MysqlDir "bin\mariadb-install-db.exe"
    $InstallDb2 = Join-Path $MysqlDir "bin\mysql_install_db.exe"
    
    if (Test-Path $InstallDb1) {
        & $InstallDb1 -d $DbDataDir 2>&1 | Out-Null
        Write-OK "Khoi tao Data thanh cong (mariadb-install-db)."
    } elseif (Test-Path $InstallDb2) {
        & $InstallDb2 -d $DbDataDir 2>&1 | Out-Null
        Write-OK "Khoi tao Data thanh cong (mysql_install_db)."
    } else {
        # Fallback cho MySQL >= 5.7
        & $MysqldBin --initialize-insecure --datadir=$DbDataDir 2>&1 | Out-Null
        Write-OK "Khoi tao Data thanh cong (mysqld --initialize-insecure)."
    }
}

$SvcName = "MariaDB-ERP"
$DbSvc = Get-Service -Name $SvcName -ErrorAction SilentlyContinue
if (-not $DbSvc) {
    Write-INFO "Dang ky Windows Service cho MariaDB..."
    & $MysqldBin --install $SvcName 2>&1 | Out-Null
    Write-OK "Da dang ky Service: $SvcName"
}

if ((Get-Service -Name $SvcName).Status -ne 'Running') {
    Start-Service -Name $SvcName
    Write-OK "Da khoi dong $SvcName"
} else {
    Write-OK "$SvcName dang hoat dong."
}

Start-Sleep -Seconds 3 # Cho DB san sang

$DbHost = "127.0.0.1"; $DbPort = "3306"; $DbUser = "root"; $DbPass = ""
$DbName = "logistics-erp-db"

Write-Host ""
Write-Divider
Write-Host "  TAI KHOAN ADMIN DAU TIEN" -ForegroundColor Yellow
Write-Divider

$AdminName = Read-Host "  Ho va ten Admin"
while ([string]::IsNullOrWhiteSpace($AdminName)) { $AdminName = Read-Host "  Ho va ten Admin" }

$AdminEmail = Read-Host "  Email Admin"
while ([string]::IsNullOrWhiteSpace($AdminEmail) -or $AdminEmail -notmatch "@") { $AdminEmail = Read-Host "  Email Admin" }

$AdminPassSecure = Read-Host "  Mat khau Admin (it nhat 8 ky tu)" -AsSecureString
$AdminPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($AdminPassSecure))
while ($AdminPass.Length -lt 8) {
    Write-WARN "Mat khau phai co it nhat 8 ky tu."
    $AdminPassSecure = Read-Host "  Mat khau Admin" -AsSecureString
    $AdminPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($AdminPassSecure))
}

# â”€â”€ Buoc 4: Tao .env â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Write-Step 4 9 "Tao file cau hinh moi truong (.env)..."
$EnvFile    = Join-Path $ProjectRoot ".env"
$EnvExample = Join-Path $ProjectRoot ".env.example"
if (-not (Test-Path $EnvFile)) {
    Copy-Item $EnvExample $EnvFile
}
$EnvContent = Get-Content $EnvFile -Raw
$EnvContent = Set-EnvValue $EnvContent "APP_NAME"    '"NT Logistics ERP"'
$EnvContent = Set-EnvValue $EnvContent "APP_URL"     "http://127.0.0.1:8000"
$EnvContent = Set-EnvValue $EnvContent "APP_ENV"     "production"
$EnvContent = Set-EnvValue $EnvContent "APP_DEBUG"   "false"
$EnvContent = Set-EnvValue $EnvContent "DB_HOST"     $DbHost
$EnvContent = Set-EnvValue $EnvContent "DB_PORT"     $DbPort
$EnvContent = Set-EnvValue $EnvContent "DB_DATABASE" $DbName
$EnvContent = Set-EnvValue $EnvContent "DB_USERNAME" $DbUser
$EnvContent = Set-EnvValue $EnvContent "DB_PASSWORD" $DbPass
$EnvContent = Set-EnvValue $EnvContent "INSTALL_ADMIN_NAME"     "`"$AdminName`""
$EnvContent = Set-EnvValue $EnvContent "INSTALL_ADMIN_EMAIL"    $AdminEmail
$EnvContent = Set-EnvValue $EnvContent "INSTALL_ADMIN_PASSWORD" $AdminPass
Set-Content $EnvFile -Value $EnvContent -Encoding UTF8
Write-OK "Da ghi cau hinh vao .env"

# â”€â”€ Buoc 5: Database & Composer â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Write-Step 5 9 "Tao Database & Cai thu vien PHP..."
$CreateDbSql = "CREATE DATABASE IF NOT EXISTS ``$DbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo $CreateDbSql | & $MysqlBin -h $DbHost -P $DbPort -u $DbUser 2>&1 | Out-Null
Write-OK "Database '$DbName' da san sang"

Set-Location $ProjectRoot
Write-INFO "Dang cai thu vien Composer (1-3 phut)..."
& $PhpBin $ComposerPhar install --no-dev --optimize-autoloader --no-interaction
Write-OK "Cai thu vien thanh cong"

# â”€â”€ Buoc 6: Migrate & Seed â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Write-Step 6 9 "Tao bang va du lieu mau..."
& $PhpBin "$ProjectRoot\artisan" key:generate --force
& $PhpBin "$ProjectRoot\artisan" migrate --force
if ($LASTEXITCODE -ne 0) { Write-ERR "Migration that bai."; exit 1 }
& $PhpBin "$ProjectRoot\artisan" db:seed --class=ProductionSeeder --force
Write-OK "Da hoan tat khoi tao du lieu"

$EnvContent = Get-Content $EnvFile -Raw
$EnvContent = $EnvContent -replace "(?m)^INSTALL_ADMIN_NAME=.*\r?\n?", ""
$EnvContent = $EnvContent -replace "(?m)^INSTALL_ADMIN_EMAIL=.*\r?\n?", ""
$EnvContent = $EnvContent -replace "(?m)^INSTALL_ADMIN_PASSWORD=.*\r?\n?", ""
Set-Content $EnvFile -Value $EnvContent.TrimEnd() -Encoding UTF8

# â”€â”€ Buoc 7: Storage & Cache â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
Write-Step 7 9 "Toi uu hoa he thong..."
& $PhpBin "$ProjectRoot\artisan" storage:link --force 2>$null
$BackupsDir = Join-Path $ProjectRoot "storage\app\backups"
if (-not (Test-Path $BackupsDir)) { New-Item -ItemType Directory -Path $BackupsDir -Force | Out-Null }
& $PhpBin "$ProjectRoot\artisan" config:cache 2>$null
& $PhpBin "$ProjectRoot\artisan" route:cache  2>$null
& $PhpBin "$ProjectRoot\artisan" view:cache   2>$null
Write-OK "Hoan tat toi uu"

# ── Buoc 8: Task Scheduler & Firewall ────────────────────────
Write-Step 8 9 "Dang ky chay ngam & Mo Firewall..."
$TaskName = "NT Logistics ERP - Auto Start Server"
$ExistingTask = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
if (-not $ExistingTask) {
    try {
        $StartScript = Join-Path (Split-Path $PSScriptRoot -Parent) "installer\start.ps1"
        $Action = New-ScheduledTaskAction -Execute "PowerShell.exe" `
            -Argument "-NoProfile -ExecutionPolicy Bypass -WindowStyle Normal -File `"$StartScript`""
        $Trigger  = New-ScheduledTaskTrigger -AtLogOn
        $Settings = New-ScheduledTaskSettingsSet -ExecutionTimeLimit 0 -MultipleInstances IgnoreNew
        Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger `
            -Settings $Settings -RunLevel Highest -Force | Out-Null
        Write-OK "Da dang ky tu dong khoi dong"
    } catch { Write-WARN "Khong the dang ky Task Scheduler" }
}

$LocalIP = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
    $_.IPAddress -ne '127.0.0.1' -and $_.PrefixOrigin -in @('Dhcp','Manual')
} | Select-Object -First 1).IPAddress
if (-not $LocalIP) { $LocalIP = "127.0.0.1" }

try {
    $RuleName = "NT Logistics ERP (Port 8000)"
    if (-not (Get-NetFirewallRule -DisplayName $RuleName -ErrorAction SilentlyContinue)) {
        New-NetFirewallRule -DisplayName $RuleName -Direction Inbound -Protocol TCP `
            -LocalPort 8000 -Action Allow -Profile Any -ErrorAction Stop | Out-Null
        Write-OK "Da mo port 8000 trong Firewall"
    }
} catch { Write-WARN "Khong the mo Firewall tu dong." }

$EnvContent = Get-Content $EnvFile -Raw
$EnvContent = Set-EnvValue $EnvContent "APP_URL" "http://${LocalIP}:8000"
Set-Content $EnvFile -Value $EnvContent.TrimEnd() -Encoding UTF8

# ── Buoc 9: Tai cloudflared cho WAN (Cloudflare Tunnel) ─────
$CloudflaredBin = Join-Path $ServerDir "cloudflared.exe"
Write-Step 9 9 "Thiet lap Cloudflare Tunnel (truy cap Internet)..."
$CloudflaredSrc = Join-Path $PrereqDir "cloudflared-windows-amd64.exe"

if (-not (Test-Path $CloudflaredBin)) {
    if (Test-Path $CloudflaredSrc) {
        Write-INFO "Copy cloudflared tu bo cai..."
        Copy-Item $CloudflaredSrc $CloudflaredBin -Force
        Write-OK "Da copy cloudflared.exe thanh cong."
    } else {
        Write-INFO "Dang tai cloudflared.exe tu Cloudflare (~30MB)..."
        try {
            $cfUrl = "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"
            Invoke-WebRequest -Uri $cfUrl -OutFile $CloudflaredBin -UseBasicParsing -TimeoutSec 180
            Write-OK "Da tai cloudflared.exe thanh cong."
        } catch {
            Write-WARN "Khong tai duoc cloudflared. Tinh nang WAN se khong hoat dong."
            Write-WARN "Kiem tra ket noi internet va chay lai start.bat de thu lai."
            $CloudflaredBin = $null
        }
    }
} else {
    Write-OK "cloudflared.exe da co san."
}

if ($CloudflaredBin -and (Test-Path $CloudflaredBin)) {


    # KHOI DONG LUON DE LAY LINK TUNNEL CHO BROWSER SAU KHI CAI DAT
    $TunnelLog = Join-Path $ProjectRoot "tunnel.log"
    Remove-Item $TunnelLog -ErrorAction SilentlyContinue
    Start-Process -FilePath $CloudflaredBin -ArgumentList "tunnel --url http://localhost:8000" -RedirectStandardError $TunnelLog -WindowStyle Hidden
    
    Write-INFO "Dang ket noi Cloudflare Tunnel de lay URL..."
    $Waited = 0
    $TunnelUrl = $null
    while ($Waited -lt 25) {
        Start-Sleep -Seconds 2
        $Waited += 2
        if (Test-Path $TunnelLog) {
            $logContent = Get-Content $TunnelLog -Raw -ErrorAction SilentlyContinue
            if ($logContent -match 'https://[a-z0-9\-]+\.trycloudflare\.com') {
                $TunnelUrl = $Matches[0]
                break
            }
        }
    }

    if ($TunnelUrl) {
        $WanUrlFile = Join-Path $ProjectRoot "wan-url.txt"
        $TunnelUrl | Set-Content $WanUrlFile -Encoding UTF8
        Write-OK "Cloudflare WAN URL: $TunnelUrl"
        
        $EnvContent = Get-Content $EnvFile -Raw
        $EnvContent = Set-EnvValue $EnvContent "APP_URL" $TunnelUrl
        Set-Content $EnvFile -Value $EnvContent.TrimEnd() -Encoding UTF8
        & $PhpBin "$ProjectRoot\artisan" config:cache 2>&1 | Out-Null
    }
}

# ── Ket qua cai dat ───────────────────────────────────────
Write-Host ""
Write-Host "  +======================================================+" -ForegroundColor Green
Write-Host "  |                                                      |" -ForegroundColor Green
Write-Host "  |   >>> CAI DAT THANH CONG! <<<                        |" -ForegroundColor Green
Write-Host "  |                                                      |" -ForegroundColor Green
Write-Host "  +======================================================+" -ForegroundColor Green
Write-Host ""
Write-Host "  Thong tin truy cap:" -ForegroundColor White
Write-Host "  +-----------------------------------------------------+" -ForegroundColor DarkGray
Write-Host "  | [LAN] May nay       : http://127.0.0.1:8000         |" -ForegroundColor Cyan
Write-Host "  | [LAN] May khac LAN  : http://${LocalIP}:8000" -ForegroundColor Cyan
if ($TunnelUrl) {
    Write-Host "  | [WAN] Internet      : $TunnelUrl" -ForegroundColor Yellow
} else {
    Write-Host "  | [WAN] Internet      : Xem man hinh khi chay start.bat" -ForegroundColor Yellow
}
Write-Host "  | Email dang nhap     : $AdminEmail" -ForegroundColor White
Write-Host "  +-----------------------------------------------------+" -ForegroundColor DarkGray
Write-Host ""
Write-Host "  Lan sau muon khoi dong: chay installer\start.bat" -ForegroundColor Green
Write-Host ""

Start-Job -ScriptBlock {
    param($TunnelUrl, $LocalIP)
    Start-Sleep -Seconds 4
    if ($TunnelUrl) {
        Start-Process $TunnelUrl
    } else {
        Start-Process "http://${LocalIP}:8000"
    }
} -ArgumentList $TunnelUrl, $LocalIP | Out-Null

Write-Host "  May chu dang khoi dong..." -ForegroundColor Green
Write-Host "  Nhan Ctrl+C de dung." -ForegroundColor DarkGray
Write-Host ""

& $PhpBin "$ProjectRoot\artisan" serve --host=0.0.0.0 --port=8000

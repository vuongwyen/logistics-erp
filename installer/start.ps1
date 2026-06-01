# ============================================================
#  NT LOGISTICS ERP -- Khoi dong May chu LAN
#  start.ps1  |  Dung hang ngay sau khi da cai dat
# ============================================================

$Host.UI.RawUI.WindowTitle = "NT Logistics ERP -- May chu LAN"

function Write-OK   { param([string]$M) Write-Host "  [OK] $M" -ForegroundColor Green }
function Write-WARN { param([string]$M) Write-Host "  [!!] $M" -ForegroundColor Yellow }
function Write-ERR  { param([string]$M) Write-Host "  [XX] $M" -ForegroundColor Red }

function Set-EnvValue {
    param([string]$Content, [string]$Key, [string]$Value)
    if ($Content -match "(?m)^${Key}=") {
        return $Content -replace "(?m)^${Key}=.*$", "${Key}=${Value}"
    }
    return $Content + "`n${Key}=${Value}"
}

function Get-EnvValue {
    param([string]$Content, [string]$Key)
    if ($Content -match "(?m)^${Key}=(.*)$") { return $Matches[1].Trim('"').Trim("'") }
    return $null
}

$ProjectRoot = Split-Path $PSScriptRoot -Parent
if (-not $ProjectRoot) { $ProjectRoot = Split-Path (Get-Location).Path -Parent }

Clear-Host
Write-Host ""
Write-Host "  +======================================================+" -ForegroundColor Cyan
Write-Host "  |    NT LOGISTICS ERP -- Khoi dong May chu LAN         |" -ForegroundColor Cyan
Write-Host "  +======================================================+" -ForegroundColor Cyan
Write-Host ""

# Kiem tra da cai dat chua
$EnvFile = Join-Path $ProjectRoot ".env"
if (-not (Test-Path $EnvFile)) {
    Write-ERR "Chua cai dat! Vui long chay install.bat truoc."
    Read-Host "  Nhan Enter de thoat"
    exit 1
}

# Tim PHP
$PhpBin = Join-Path $ProjectRoot "server\php\php.exe"
if (-not (Test-Path $PhpBin)) {
    Write-ERR "Khong tim thay PHP Portable. Dam bao ban da chay install.bat thanh cong."
    Read-Host "  Nhan Enter de thoat"
    exit 1
}

# Kiem tra MySQL
$EnvContent = Get-Content $EnvFile -Raw
$DbHost = Get-EnvValue $EnvContent "DB_HOST"
$DbPort = Get-EnvValue $EnvContent "DB_PORT"
$DbUser = Get-EnvValue $EnvContent "DB_USERNAME"
$DbPass = Get-EnvValue $EnvContent "DB_PASSWORD"
$DbName = Get-EnvValue $EnvContent "DB_DATABASE"

$MysqlBin = Join-Path $ProjectRoot "server\mysql\bin\mysql.exe"

if ($MysqlBin -and $DbHost) {
    Write-Host "  Dang kiem tra ket noi MySQL..." -ForegroundColor DarkGray
    if ($DbPass) {
        $TestResult = & "$MysqlBin" -h $DbHost -P $DbPort -u $DbUser "--password=$DbPass" --connect-timeout=3 -e "SELECT 1;" 2>&1
    } else {
        $TestResult = & "$MysqlBin" -h $DbHost -P $DbPort -u $DbUser --connect-timeout=3 -e "SELECT 1;" 2>&1
    }

    if ($LASTEXITCODE -ne 0) {
        Write-WARN "MariaDB chua khoi dong! Service MariaDB-ERP co the dang dung."
        Write-Host ""
        $Continue = Read-Host "  Tiep tuc khoi dong server khong? (y/N)"
        if ($Continue -ne 'y' -and $Continue -ne 'Y') { exit 1 }
    } else {
        Write-OK "MySQL dang chay -- Database: $DbName"
    }
}

# Lay IP LAN
$LocalIP = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
    $_.IPAddress -ne '127.0.0.1' -and $_.PrefixOrigin -in @('Dhcp','Manual')
} | Select-Object -First 1).IPAddress
if (-not $LocalIP) { $LocalIP = "127.0.0.1" }

# Khong the cap nhat APP_URL thanh IP LAN nua, de phan duoi cap nhat tunnel

# Mo Firewall neu can
$RuleName = "NT Logistics ERP (Port 8000)"
if (-not (Get-NetFirewallRule -DisplayName $RuleName -ErrorAction SilentlyContinue)) {
    try {
        New-NetFirewallRule -DisplayName $RuleName -Direction Inbound -Protocol TCP `
            -LocalPort 8000 -Action Allow -Profile Any -ErrorAction Stop | Out-Null
        Write-OK "Da mo Firewall port 8000"
    } catch {
        Write-WARN "Khong the mo Firewall tu dong. Can chay voi quyen Administrator."
    }
}

# ── Khoi dong Cloudflare Quick Tunnel (WAN) ───────────────────
$CloudflaredBin = Join-Path $ProjectRoot "server\cloudflared.exe"
$TunnelLog      = Join-Path $ProjectRoot "tunnel.log"
$WanUrlFile     = Join-Path $ProjectRoot "wan-url.txt"
$TunnelUrl      = $null

if (-not (Test-Path $CloudflaredBin)) {
    Write-WARN "Chua co cloudflared.exe. Dang thu tai..."
    try {
        $cfUrl = "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"
        Invoke-WebRequest -Uri $cfUrl -OutFile $CloudflaredBin -UseBasicParsing -TimeoutSec 180
        Write-OK "Da tai cloudflared.exe."
    } catch {
        Write-WARN "Khong tai duoc cloudflared. He thong chi hoat dong trong LAN."
        $CloudflaredBin = $null
    }
}

if ($CloudflaredBin -and (Test-Path $CloudflaredBin)) {
    # Xoa log cu de bat dau moi
    Remove-Item $TunnelLog -ErrorAction SilentlyContinue

    # Chay cloudflared trong nen, ghi stderr (chua URL) vao file log
    Start-Process -FilePath $CloudflaredBin `
        -ArgumentList "tunnel --url http://localhost:8000" `
        -RedirectStandardError $TunnelLog `
        -WindowStyle Hidden

    Write-Host "  Dang ket noi Cloudflare Tunnel..." -ForegroundColor DarkGray

    # Cho URL xuat hien trong log (toi da 25 giay)
    $Waited = 0
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
        # Luu URL vao file de tham khao va chia se
        $TunnelUrl | Set-Content $WanUrlFile -Encoding UTF8
        Write-OK "WAN: $TunnelUrl"

        # Gap link tunnel vao .env va xoa cache
        $EnvContent = Get-Content $EnvFile -Raw
        $EnvContent = Set-EnvValue $EnvContent "APP_URL" $TunnelUrl
        Set-Content $EnvFile -Value $EnvContent.TrimEnd() -Encoding UTF8
        
        Write-INFO "Cap nhat cau hinh he thong (APP_URL)..."
        & "$PhpBin" "$ProjectRoot\artisan" config:cache 2>&1 | Out-Null
    } else {
        Write-WARN "Khong lay duoc URL tunnel (kiem tra internet). Chi dung duoc LAN."
    }
}

# ── Ham hien QR Code ASCII tu qrcode.show API ────────────────
function Show-QRCode {
    param([string]$Url, [string]$Color = "White")
    try {
        $r = Invoke-WebRequest -Uri "https://qrcode.show/$Url" `
            -Headers @{Accept = "text/plain"} `
            -UseBasicParsing -TimeoutSec 8 -ErrorAction Stop
        # In tung dong, them khoang trang de can giua
        ($r.Content -split "`n") | ForEach-Object {
            Write-Host "    $_" -ForegroundColor $Color
        }
    } catch {
        # Fallback: neu khong co internet thi chi in URL
        Write-Host "    $Url" -ForegroundColor DarkGray
        Write-Host "    (Khong hien thi QR - kiem tra internet)" -ForegroundColor DarkGray
    }
}

# ── Mo trinh duyet sau 2 giay ─────────────────────────────────
Start-Job -ScriptBlock {
    param($TunnelUrl, $ip)
    Start-Sleep -Seconds 4
    if ($TunnelUrl) {
        Start-Process $TunnelUrl
    } else {
        Start-Process "http://${ip}:8000"
    }
} -ArgumentList $TunnelUrl, $LocalIP | Out-Null

# ── Hien thi thong tin & QR Code ─────────────────────────────
Write-Host ""
Write-Host "  +========================================================+" -ForegroundColor Green
Write-Host "  |             THONG TIN TRUY CAP HE THONG               |" -ForegroundColor Green
Write-Host "  +========================================================+" -ForegroundColor Green
Write-Host "  | [LAN] Tren may nay   : http://127.0.0.1:8000          |" -ForegroundColor Cyan
Write-Host "  | [LAN] May khac (LAN) : http://${LocalIP}:8000" -ForegroundColor Cyan
if ($TunnelUrl) {
    Write-Host "  | [WAN] Internet (4G)  : $TunnelUrl" -ForegroundColor Yellow
    Write-Host "  | [WAN] Luu o file     : wan-url.txt                     |" -ForegroundColor DarkGray
} else {
    Write-Host "  | [WAN] Internet       : (chua ket noi duoc tunnel)      |" -ForegroundColor DarkGray
}
Write-Host "  +========================================================+" -ForegroundColor Green

Write-Host ""
Write-Host "  --- QR CODE LAN (nhan vien trong van phong) ---" -ForegroundColor Cyan
Show-QRCode "http://${LocalIP}:8000" "Cyan"

if ($TunnelUrl) {
    Write-Host ""
    Write-Host "  --- QR CODE WAN (tai xe / truy cap tu xa qua 4G) ---" -ForegroundColor Yellow
    Show-QRCode $TunnelUrl "Yellow"
    Write-Host ""
    Write-Host "  LUU Y: URL nay thay doi moi lan khoi dong." -ForegroundColor DarkGray
    Write-Host "  De co URL co dinh, can domain rieng + Cloudflare account." -ForegroundColor DarkGray
}

Write-Host ""
Write-Host "  Nhan Ctrl+C de tat may chu." -ForegroundColor DarkGray
Write-Host ""

# ── Khoi chay Laravel server (blocking) ──────────────────────
& "$PhpBin" "$ProjectRoot\artisan" serve --host=0.0.0.0 --port=8000

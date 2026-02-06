param(
    [string]$ServiceName = "HotelITMessaging",
    [string]$PythonPath = "python",
    [string]$WorkingDir = (Split-Path -Parent $MyInvocation.MyCommand.Path)
)

Write-Host "Installing messaging server as service..."
$nssm = Join-Path $WorkingDir "nssm.exe"
if (-not (Test-Path $nssm)) {
    Write-Host "nssm.exe not found. Place nssm.exe in the server_messaging folder."
    exit 1
}

$serverScript = Join-Path $WorkingDir "server.py"
& $nssm install $ServiceName $PythonPath $serverScript
& $nssm set $ServiceName AppDirectory $WorkingDir
& $nssm start $ServiceName
Write-Host "Service installed and started."

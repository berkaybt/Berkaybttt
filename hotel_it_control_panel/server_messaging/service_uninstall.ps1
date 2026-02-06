param(
    [string]$ServiceName = "HotelITMessaging"
)

Write-Host "Stopping and removing service $ServiceName"
$service = Get-Service -Name $ServiceName -ErrorAction SilentlyContinue
if ($service) {
    Stop-Service -Name $ServiceName -Force
    sc.exe delete $ServiceName | Out-Null
    Write-Host "Service removed."
} else {
    Write-Host "Service not found."
}

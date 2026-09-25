# Copy the local XAMPP database onto the Railway MySQL service.
# Usage (from the project root, after changing data in phpMyAdmin):
#   powershell -ExecutionPolicy Bypass -File .\scripts\sync-xampp-to-railway.ps1
param(
    [string]$Database = "farm_market",
    [string]$MysqlUser = "root",
    [string]$MysqlPassword = "",
    [int]$LocalPort = 3307
)

$ErrorActionPreference = "Stop"
$dump = Join-Path $env:TEMP "farm_market_railway.sql"
$mysqldump = "C:\xampp\mysql\bin\mysqldump.exe"
$php = "C:\xampp\php\php.exe"

if (-not (Test-Path $mysqldump)) { throw "Khong tim thay mysqldump tai $mysqldump" }

$dumpArgs = @("-u", $MysqlUser, "--single-transaction", "--routines", "--triggers", "--default-character-set=utf8mb4", $Database)
if ($MysqlPassword -ne "") { $dumpArgs = @("-u", $MysqlUser, "-p$MysqlPassword") + $dumpArgs[2..($dumpArgs.Length - 1)] }

& $mysqldump @dumpArgs -r $dump
if ($LASTEXITCODE -ne 0) { throw "Export XAMPP that bai" }

$tunnel = Start-Process -FilePath "railway" -ArgumentList @("connect", "MySQL", "--tunnel-only", "-P", "$LocalPort") -PassThru -WindowStyle Hidden
try {
    $ready = $false
    for ($i = 0; $i -lt 30; $i++) {
        Start-Sleep -Seconds 1
        $probe = Test-NetConnection -ComputerName 127.0.0.1 -Port $LocalPort -WarningAction SilentlyContinue
        if ($probe.TcpTestSucceeded) { $ready = $true; break }
    }
    if (-not $ready) { throw "Khong mo duoc tunnel Railway" }

    $vars = railway variables --service MySQL --json | ConvertFrom-Json
    $env:MYSQL_PWD = $vars.MYSQLPASSWORD

    $importPhp = Join-Path $env:TEMP "import_railway.php"
    @'
<?php
$mysqli = new mysqli("127.0.0.1", "root", getenv("MYSQL_PWD"), "railway", (int) getenv("MYSQL_PORT"));
if ($mysqli->connect_error) { fwrite(STDERR, $mysqli->connect_error); exit(1); }
$mysqli->set_charset("utf8mb4");
$mysqli->query("SET FOREIGN_KEY_CHECKS=0");
$tables = $mysqli->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'railway'");
$names = [];
while ($row = $tables->fetch_row()) { $names[] = '`' . $row[0] . '`'; }
if ($names) {
    $mysqli->query("DROP TABLE IF EXISTS ".implode(",", $names));
}
$sql = file_get_contents(getenv("SQL_FILE"));
if (!$mysqli->multi_query($sql)) { fwrite(STDERR, $mysqli->error); exit(1); }
do {
    if ($result = $mysqli->store_result()) { $result->free(); }
    if ($mysqli->errno) { fwrite(STDERR, $mysqli->error.PHP_EOL); exit(1); }
} while ($mysqli->more_results() && $mysqli->next_result());
echo "SYNC_OK\n";
'@ | Set-Content -Encoding ASCII $importPhp

    $env:MYSQL_PORT = "$LocalPort"
    $env:SQL_FILE = $dump
    & $php $importPhp
    if ($LASTEXITCODE -ne 0) { throw "Import Railway that bai" }
}
finally {
    Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    if ($tunnel -and -not $tunnel.HasExited) { Stop-Process -Id $tunnel.Id -Force }
}

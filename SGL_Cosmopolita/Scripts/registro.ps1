# Script PowerShell: Diagnóstico del PC y exportación en formato TOML

# Función para convertir PrefixLength a máscara decimal
function Convert-PrefixToSubnetMask {
    param([int]$PrefixLength)
    
    $binaryString = ('1' * $PrefixLength).PadRight(32, '0')
    $octet1 = [convert]::ToInt32($binaryString.Substring(0, 8), 2)
    $octet2 = [convert]::ToInt32($binaryString.Substring(8, 8), 2)
    $octet3 = [convert]::ToInt32($binaryString.Substring(16, 8), 2)
    $octet4 = [convert]::ToInt32($binaryString.Substring(24, 8), 2)
    
    return "$octet1.$octet2.$octet3.$octet4"
}

# Verificacion SSH-------------------------------------------------------------------------------------
# Ruta de la carpeta .ssh del usuario actual
$sshFolder = "$env:USERPROFILE\.ssh"
# Verifica si la carpeta existe
if (Test-Path $sshFolder) {
    # Busca cualquier archivo dentro de la carpeta .ssh
    $sshKeys = Get-ChildItem -Path $sshFolder -File
    if ($sshKeys.Count -gt 0) {
        Write-Host "Se encontraron claves SSH en la carpeta .ssh:"
        $sshKeys | ForEach-Object { Write-Host " - $($_.Name)" }
    } else {
        Write-Host "No se encontraron claves SSH, generando una nueva..." -ForegroundColor Cyan
        $keyFile = Join-Path $sshFolder "id_ed25519"
        # Generar clave ed25519 sin passphrase de forma no interactiva
        cmd /c "echo y|ssh-keygen -t ed25519 -f `"$keyFile`" -N `""""
        if ((Test-Path $keyFile) -and (Test-Path "$keyFile.pub")) {
            Write-Host "Claves SSH creadas exitosamente en $sshFolder" -ForegroundColor Green
        } else {
            Write-Host "Hubo un problema al crear las claves SSH." -ForegroundColor Red
        }
    }
} else {
    # Crear la carpeta .ssh si no existe
    New-Item -Path $sshFolder -ItemType Directory -Force
    Write-Host "Carpeta .ssh creada. Generando nueva clave SSH..." -ForegroundColor Cyan
    
    $keyFile = Join-Path $sshFolder "id_ed25519"
    cmd /c "echo y|ssh-keygen -t ed25519 -f `"$keyFile`" -N `""""
    if ((Test-Path $keyFile) -and (Test-Path "$keyFile.pub")) {
        Write-Host "Claves SSH creadas exitosamente en $sshFolder" -ForegroundColor Green
    } else {
        Write-Host "Hubo un problema al crear las claves SSH." -ForegroundColor Red
    }
}
# Verificacion SSH-------------------------------------------------------------------------------------

# Obtener fecha y nombre del equipo
$fecha = Get-Date -Format "yyyyMMdd"
$nombrePC = $env:COMPUTERNAME
$serial = (Get-WmiObject -Class Win32_BIOS).SerialNumber
$usuario_servidor = "enzo"
$ip_servidor = "192.168.1.61"
$ruta_registro = ":/home/enzo/registro"

# Desktop path
$Env:USERPROFILE = [Environment]::GetFolderPath("Desktop")

# Intentar hacer ping al Gateway
$gateway = (Get-NetRoute -DestinationPrefix "0.0.0.0/0").NextHop
$ping = Test-Connection -ComputerName $gateway -Count 1 -Quiet
if ($ping) {
    $estado = "Exito"
} else {
    $estado = "Fallo"
}

# Obtener información del sistema
$so = Get-CimInstance Win32_OperatingSystem

# Obtener información de red - filtrar mejor para evitar múltiples interfaces
$ipInfo = Get-NetIPAddress -AddressFamily IPv4 | 
    Where-Object {($_.PrefixOrigin -eq "Dhcp" -or $_.PrefixOrigin -eq "Manual") -and 
                  $_.IPAddress -ne "127.0.0.1" -and 
                  $_.AddressState -eq "Preferred"} | 
    Select-Object -First 1

$cpu = Get-CimInstance Win32_Processor
$ram = [math]::Round($so.TotalVisibleMemorySize / 1024 / 1024, 2) # en GB

# Información del disco con espacio libre
$disco = Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='C:'"
$discoTotal = [math]::Round($disco.Size / 1GB, 2)
$discoLibre = [math]::Round($disco.FreeSpace / 1GB, 2)

# Convertir máscara de red
if ($ipInfo -and $ipInfo.PrefixLength) {
    $mascaraDecimal = Convert-PrefixToSubnetMask -PrefixLength $ipInfo.PrefixLength
} else {
    $mascaraDecimal = "255.255.255.0"  # Valor por defecto
    Write-Warning "No se pudo obtener la máscara de red, usando valor por defecto"
}

# Detectar ejecución automática
# Si no hay host interactivo (por ejemplo, en startup), se usa cédula por defecto
if ($Host.Name -ne "ConsoleHost") {
    $cedula = "11111111"
} else {
    # Solicitar la cédula del usuario
    $cedula = Read-Host "Por favor, ingrese su cedula (sin puntos ni guiones)"
}

# Ruta y nombre de archivo de salida
$archivo = "$fecha-$nombrePC-$estado.toml"

# Crear contenido TOML
$contenido = @"
[info_general]
fecha = "$($fecha)"
serial_number = "$($serial)"
nombre_pc = "$($nombrePC)"
estado_conexion = "$($estado)"
cedula = "$($cedula)"

[sistema_operativo]
nombre = "$($so.Caption)"
version = "$($so.Version)"

[red]
ip = "$($ipInfo.IPAddress)"
mascara = "$($mascaraDecimal)"
default_gateway = "$($gateway)"

[hardware]
cpu = "$($cpu.Name)"
ram_gb = $ram
disco_c_total_gb = $discoTotal
disco_c_libre_gb = $discoLibre
"@

$desktopPath = Join-Path -Path $PWD -ChildPath $archivo

# Guardar archivo
$contenido | Out-File -FilePath $desktopPath -Encoding utf8

# (Opcional) Subir con SCP
scp $archivo $usuario_servidor@$ip_servidor$ruta_registro


Write-Output "Archivo generado: $archivo"

# Elimina archivos .tom con más de 7 días en la carpeta actual
# Obtener la fecha límite (hoy - 7 días)
$fechaLimite = (Get-Date).AddDays(-7)

# Buscar y eliminar archivos .tom con antigüedad mayor a 7 días
Get-ChildItem -Path . -Filter *.tom -File | Where-Object { $_.LastWriteTime -lt $fechaLimite } | Remove-Item -Force

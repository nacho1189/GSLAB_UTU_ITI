<?php
// Clase Equipos
class Equipos {
    private $serialNumber;
    private $hostName;
    private $CPU;
    private $RAM;
    private $diskType;
    private $diskTotal;
    
    public function __construct($serialNumber = null, $hostName = null) {
        $this->serialNumber = $serialNumber;
        $this->hostName = $hostName;
    }
    
    // Métodos
    public function controladorEquipo() {
        // Implementar lógica del controlador de equipo
    }
    
    public function listarEquipos($id) {
        // Implementar lógica para listar equipos
    }
    
    // Getters y Setters
    public function getSerialNumber() { return $this->serialNumber; }
    public function setSerialNumber($serialNumber) { $this->serialNumber = $serialNumber; }
    public function getHostName() { return $this->hostName; }
    public function setHostName($hostName) { $this->hostName = $hostName; }
    public function getCPU() { return $this->CPU; }
    public function setCPU($CPU) { $this->CPU = $CPU; }
    public function getRAM() { return $this->RAM; }
    public function setRAM($RAM) { $this->RAM = $RAM; }
    public function getDiskType() { return $this->diskType; }
    public function setDiskType($diskType) { $this->diskType = $diskType; }
    public function getDiskTotal() { return $this->diskTotal; }
    public function setDiskTotal($diskTotal) { $this->diskTotal = $diskTotal; }
}
?>
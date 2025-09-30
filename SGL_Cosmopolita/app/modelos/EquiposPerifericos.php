<?php
// Clase EquiposPerifericos
class EquiposPerifericos {
    private $id;
    private $serialNumber;
    private $idTipoPeriferico;
    private $descripcion;
    
    public function __construct($id = null) {
        $this->id = $id;
    }
    
    // Getters y Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getSerialNumber() { return $this->serialNumber; }
    public function setSerialNumber($serialNumber) { $this->serialNumber = $serialNumber; }
    public function getIdTipoPeriferico() { return $this->idTipoPeriferico; }
    public function setIdTipoPeriferico($idTipoPeriferico) { $this->idTipoPeriferico = $idTipoPeriferico; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
}
?>
<?php
// Clase TipoUsuario (ENUM)
class TipoUsuario {
    const ESTUDIANTE = 1;
    const ADMINISTRADOR = 2;
    const ASISTENTE = 3;
    const DOCENTE = 4;
    
    private $id;
    private $descripcion;
    
    public function __construct($id = null, $descripcion = null) {
        $this->id = $id;
        $this->descripcion = $descripcion;
    }
    
    // Getters y Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
}
?>
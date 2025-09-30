<?php
// Clase Laboratorios
class Laboratorios {
    private $id;
    private $nombre;
    private $comentario;
    
    public function __construct($id = null, $nombre = null) {
        $this->id = $id;
        $this->nombre = $nombre;
    }
    
    // Métodos
    public function listarEquipos($laboratorio) {
        // Implementar lógica para listar equipos de un laboratorio
    }
    
    public function listarLaboratorio() {
        // Implementar lógica para listar laboratorios
    }
    
    // Getters y Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getComentario() { return $this->comentario; }
    public function setComentario($comentario) { $this->comentario = $comentario; }
}
?>
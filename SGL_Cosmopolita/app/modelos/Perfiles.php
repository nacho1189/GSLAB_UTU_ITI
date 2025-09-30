<?php
// Clase Perfiles
class Perfiles {
    private $id;
    private $rol;
    private $tipoUsuario;
    private $permisos;
    private $idTipoUsuario;
    
    public function __construct($id = null) {
        $this->id = $id;
    }
    
    // Getters y Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getRol() { return $this->rol; }
    public function setRol($rol) { $this->rol = $rol; }
    public function getTipoUsuario() { return $this->tipoUsuario; }
    public function setTipoUsuario($tipoUsuario) { $this->tipoUsuario = $tipoUsuario; }
    public function getPermisos() { return $this->permisos; }
    public function setPermisos($permisos) { $this->permisos = $permisos; }
    public function getIdTipoUsuario() { return $this->idTipoUsuario; }
    public function setIdTipoUsuario($idTipoUsuario) { $this->idTipoUsuario = $idTipoUsuario; }
}
?>
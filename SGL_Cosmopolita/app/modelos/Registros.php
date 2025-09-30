<?php
// Clase Registros
class Registros {
    private $id;
    private $fecha;
    private $estado;
    private $diskFree;
    private $descripcion;
    private $idUsuario;
    private $hostNamePC;
    private $idLab;
    
    public function __construct($id = null) {
        $this->id = $id;
        $this->fecha = date('Y-m-d H:i:s');
        $this->estado = true;
        $this->idLab = 6; // Hardcodeado como laboratorio 6
    }
    
    // Método para crear un nuevo registro en la base de datos
    public function crearRegistro($conexion) {
        try {
            $query = "INSERT INTO Registros (fecha, descripcion, estado, diskFree, idUsuario, hostNamePC, idLab) 
                      VALUES (NOW(), ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conexion, $query);
            
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conexion));
            }
            
            mysqli_stmt_bind_param($stmt, "siissi", 
                $this->descripcion,
                $this->estado, 
                $this->diskFree, 
                $this->idUsuario, 
                $this->hostNamePC,
                $this->idLab
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $this->id = mysqli_insert_id($conexion);
                mysqli_stmt_close($stmt);
                return true;
            } else {
                mysqli_stmt_close($stmt);
                throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
            }
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    // Método para validar los datos antes de insertar
    public function validarDatos() {
        $errores = array();
        
        if (empty($this->hostNamePC)) {
            $errores[] = "El hostNamePC es obligatorio";
        }
        
        if (empty($this->idLab)) {
            $errores[] = "El idLab es obligatorio";
        }
        
        if ($this->estado !== 0 && $this->estado !== 1) {
            $errores[] = "El estado debe ser 0 o 1";
        }
        
        if (!is_null($this->diskFree) && (!is_numeric($this->diskFree) || $this->diskFree < 0)) {
            $errores[] = "El espacio libre en disco debe ser un número válido mayor o igual a 0";
        }
        
        if (empty(trim($this->descripcion))) {
            $errores[] = "La descripción es obligatoria";
        }
        
        if (empty($this->idUsuario) || !is_numeric($this->idUsuario)) {
            $errores[] = "ID de usuario no válido";
        }
        
        return $errores;
    }
    
    // Método para cargar datos desde un array (útil para formularios)
    public function cargarDatos($datos) {
        if (isset($datos['hostNamePC'])) {
            $this->setHostNamePC(trim($datos['hostNamePC']));
        }
        
        if (isset($datos['estado'])) {
            $this->setEstado($datos['estado'] == '1' ? 1 : 0);
        }
        
        if (isset($datos['diskFree'])) {
            $this->setDiskFree((int)$datos['diskFree']);
        }
        
        if (isset($datos['descripcion'])) {
            $this->setDescripcion(trim($datos['descripcion']));
        }
        
        if (isset($datos['idUsuario'])) {
            $this->setIdUsuario((int)$datos['idUsuario']);
        }
        
        // idLab siempre será 6, pero se puede cargar si viene en los datos
        if (isset($datos['idLab'])) {
            $this->setIdLab((int)$datos['idLab']);
        }
    }
    
    // Métodos específicos del dominio
    public function registroBoot($equipo) {
        // Implementar lógica de registro de boot
        return true;
    }
    
    public static function obtenerUltimosRegistrosPorLab($conexion, $laboratorio = "LAB6") {
        try {
            // Consulta que obtiene el último registro de cada PC
            $query = "
                SELECT r1.hostNamePC, r1.fecha, r1.estado, r1.descripcion, r1.idUsuario
                FROM Registros r1
                INNER JOIN (
                    SELECT hostNamePC, MAX(fecha) as max_fecha
                    FROM Registros 
                    WHERE hostNamePC LIKE ?
                    GROUP BY hostNamePC
                ) r2 ON r1.hostNamePC = r2.hostNamePC AND r1.fecha = r2.max_fecha
                ORDER BY r1.hostNamePC
            ";
            
            $stmt = mysqli_prepare($conexion, $query);
            
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conexion));
            }
            
            $patron = $laboratorio . "-%";
            mysqli_stmt_bind_param($stmt, "s", $patron);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
            }
            
            $resultado = mysqli_stmt_get_result($stmt);
            $registros = array();
            
            while ($fila = mysqli_fetch_assoc($resultado)) {
                // Extraer el número de PC del hostNamePC (ej: LAB6-PC1 -> PC1)
                $numeroPC = str_replace($laboratorio . "-", "", $fila['hostNamePC']);
                
                $registros[$numeroPC] = array(
                    'hostNamePC' => $fila['hostNamePC'],
                    'fecha' => $fila['fecha'],
                    'estado' => (bool)$fila['estado'],
                    'descripcion' => $fila['descripcion'],
                    'idUsuario' => $fila['idUsuario'],
                    'numeroPC' => $numeroPC
                );
            }
            
            mysqli_stmt_close($stmt);
            return $registros;
            
        } catch (Exception $e) {
            error_log("Error en obtenerUltimosRegistrosPorLab: " . $e->getMessage());
            throw $e;
        }
    }

    public static function obtenerUltimoRegistroPC($conexion, $hostNamePC) {
        try {
            $query = "
                SELECT hostNamePC, fecha, estado, descripcion, idUsuario 
                FROM Registros 
                WHERE hostNamePC = ? 
                ORDER BY fecha DESC 
                LIMIT 1
            ";
            
            $stmt = mysqli_prepare($conexion, $query);
            
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($conexion));
            }
            
            mysqli_stmt_bind_param($stmt, "s", $hostNamePC);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
            }
            
            $resultado = mysqli_stmt_get_result($stmt);
            $registro = mysqli_fetch_assoc($resultado);
            
            mysqli_stmt_close($stmt);
            
            if ($registro) {
                $registro['estado'] = (bool)$registro['estado'];
            }
            
            return $registro;
            
        } catch (Exception $e) {
            error_log("Error en obtenerUltimoRegistroPC: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Getters y Setters
    public function getId() { 
        return $this->id; 
    }
    
    public function setId($id) { 
        $this->id = $id; 
    }
    
    public function getFecha() { 
        return $this->fecha; 
    }
    
    public function setFecha($fecha) { 
        $this->fecha = $fecha; 
    }
    
    public function getEstado() { 
        return $this->estado; 
    }
    
    public function setEstado($estado) { 
        $this->estado = (int)$estado; 
    }
    
    public function getDiskFree() { 
        return $this->diskFree; 
    }
    
    public function setDiskFree($diskFree) { 
        $this->diskFree = $diskFree; 
    }
    
    public function getDescripcion() { 
        return $this->descripcion; 
    }
    
    public function setDescripcion($descripcion) { 
        $this->descripcion = $descripcion; 
    }
    
    public function getIdUsuario() { 
        return $this->idUsuario; 
    }
    
    public function setIdUsuario($idUsuario) { 
        $this->idUsuario = (int)$idUsuario; 
    }
    
    public function getHostNamePC() { 
        return $this->hostNamePC; 
    }
    
    public function setHostNamePC($hostNamePC) { 
        $this->hostNamePC = $hostNamePC; 
    }
    
    public function getIdLab() { 
        return $this->idLab; 
    }
    
    public function setIdLab($idLab) { 
        $this->idLab = (int)$idLab; 
    }
    
    // Método para obtener datos como array (útil para debugging o logs)
    public function toArray() {
        return array(
            'id' => $this->id,
            'fecha' => $this->fecha,
            'estado' => $this->estado,
            'diskFree' => $this->diskFree,
            'descripcion' => $this->descripcion,
            'idUsuario' => $this->idUsuario,
            'hostNamePC' => $this->hostNamePC,
            'idLab' => $this->idLab
        );
    }
}
?>
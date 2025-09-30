<?php
require_once '../config/config.php';

// Clase Usuarios
class Usuarios {
    private $id;
    private $nombre;
    private $email;
    private $password;
    private $rol;
    private $ci;
    private $idTipoUsuario;
    
    public function __construct($id = null, $nombre = null, $email = null, $password = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
    }
    
    //Metodos

    public function iniciarSesionUsuario($link, $ci) {
    // Verificar si existe el usuario con esa cédula y rol de estudiante o docente (rol = 1 o 4)
    $query = "SELECT id, ci, nombre, email, rol FROM Usuarios WHERE ci = ? AND (rol = 1 OR rol = 4)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "s", $ci);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Usuario encontrado - obtener sus datos
        $userData = mysqli_fetch_assoc($result);
        
        // Guardar los datos en la sesión
        session_start();
        $_SESSION['usuario_id'] = $userData['id'];
        $_SESSION['usuario_ci'] = $userData['ci'];
        $_SESSION['usuario_nombre'] = $userData['nombre'];
        $_SESSION['usuario_rol'] = $userData['rol'];
        
        return true; // Login exitoso
    } else {
        return false; // Usuario no encontrado o no es estudiante
    }
}

public function iniciarSesionDocente($link, $ci) {
    // Verificar si existe el usuario con esa cédula y rol de docente (rol = 4)
    $query = "SELECT id, ci, nombre, email, rol FROM Usuarios WHERE ci = ? AND rol = 4";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "s", $ci);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Usuario encontrado - obtener sus datos
        $userData = mysqli_fetch_assoc($result);
        
        // Guardar los datos en la sesión
        session_start();
        $_SESSION['usuario_id'] = $userData['id'];
        $_SESSION['usuario_ci'] = $userData['ci'];
        $_SESSION['usuario_nombre'] = $userData['nombre'];
        $_SESSION['usuario_rol'] = $userData['rol'];
        
        return true; // Login exitoso
    } else {
        return false; // Usuario no encontrado o no es estudiante
    }
}

    public function iniciarSesionAdmin($link, $ci, $password) {
    // Verificar que ambos campos no estén vacíos
    if (empty($ci) || empty($password)) {
        return false;
    }
    
    // Verificar si existe el admin con esa cédula, contraseña y rol de admin (rol = 2)
    $query = "SELECT id, ci, nombre, email, rol FROM Usuarios WHERE ci = ? AND password = ? AND rol = 2";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ss", $ci, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Admin encontrado - obtener sus datos
        $adminData = mysqli_fetch_assoc($result);
        
        // Guardar los datos en la sesión
        session_start();
        $_SESSION['admin_id'] = $adminData['id'];
        $_SESSION['admin_ci'] = $adminData['ci'];
        $_SESSION['admin_nombre'] = $adminData['nombre'];
        $_SESSION['admin_rol'] = $adminData['rol'];
        
        return true; // Login exitoso
    } else {
        return false; // Admin no encontrado o credenciales incorrectas
    }
    }

    public function iniciarSesionAdminReal($link, $ci, $password) {
    // Verificar que ambos campos no estén vacíos
    if (empty($ci) || empty($password)) {
        return false;
    }
    
    // Verificar si existe el admin con esa cédula, contraseña y rol de admin (rol = 3)
    $query = "SELECT id, ci, nombre, email, rol FROM Usuarios WHERE ci = ? AND password = ? AND rol = 3";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ss", $ci, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Admin encontrado - obtener sus datos
        $adminData = mysqli_fetch_assoc($result);
        
        // Guardar los datos en la sesión
        session_start();
        $_SESSION['admin_id'] = $adminData['id'];
        $_SESSION['admin_ci'] = $adminData['ci'];
        $_SESSION['admin_nombre'] = $adminData['nombre'];
        $_SESSION['admin_rol'] = $adminData['rol'];
        
        return true; // Login exitoso
    } else {
        return false; // Admin no encontrado o credenciales incorrectas
    }
    }

    public function altaUsuario($link, $nombre, $email, $password, $ci, $rol) {
    // Verificar que los campos obligatorios no estén vacíos (email ya no es obligatorio)
    if (empty($nombre) || empty($ci) || empty($rol)) {
        return false;
    }
    
    // Verificar que el email no esté ya registrado (solo si se proporciona)
    if (!empty($email)) {
        $queryCheck = "SELECT id FROM Usuarios WHERE email = ?";
        $stmtCheck = mysqli_prepare($link, $queryCheck);
        mysqli_stmt_bind_param($stmtCheck, "s", $email);
        mysqli_stmt_execute($stmtCheck);
        $resultCheck = mysqli_stmt_get_result($stmtCheck);
        
        if (mysqli_num_rows($resultCheck) > 0) {
            return false; // Email ya existe
        }
    }
    
    // Verificar que la cédula no esté ya registrada
    $queryCheckCI = "SELECT id FROM Usuarios WHERE ci = ?";
    $stmtCheckCI = mysqli_prepare($link, $queryCheckCI);
    mysqli_stmt_bind_param($stmtCheckCI, "s", $ci);
    mysqli_stmt_execute($stmtCheckCI);
    $resultCheckCI = mysqli_stmt_get_result($stmtCheckCI);
    
    if (mysqli_num_rows($resultCheckCI) > 0) {
        return false; // Cédula ya existe
    }
    
    // Preparar la consulta de inserción
    if ($rol == 2 || $rol == 3) {
        // Para Asistente (rol 2) o Administrador (rol 3) se requiere contraseña
        if (empty($password)) {
            return false;
        }
        if (!empty($email)) {
            $query = "INSERT INTO Usuarios (nombre, email, password, ci, rol) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $email, $password, $ci, $rol);
        } else {
            $query = "INSERT INTO Usuarios (nombre, password, ci, rol) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $nombre, $password, $ci, $rol);
        }
    } else {
        // Para Estudiante (rol 1) o Docente (rol 4) no se requiere contraseña
        if (!empty($email)) {
            $query = "INSERT INTO Usuarios (nombre, email, ci, rol) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $ci, $rol);
        } else {
            $query = "INSERT INTO Usuarios (nombre, ci, rol) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $nombre, $ci, $rol);
        }
    }
    
    // Ejecutar la inserción
    if (mysqli_stmt_execute($stmt)) {
        return true; // Usuario creado exitosamente
    } else {
        return false; // Error al crear usuario
    }
}

    public function consultaUsuarios($link) {
    // Consulta para obtener todos los usuarios
    $query = "SELECT id, nombre, email, password, ci, rol FROM Usuarios ORDER BY id ASC";
    $stmt = mysqli_prepare($link, $query);
    
    if (!$stmt) {
        return false; // Error en la preparación
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $usuarios = array();
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Convertir el número de rol a texto legible
            $rolTexto = '';
            switch($row['rol']) {
                case 1:
                    $rolTexto = 'Estudiante';
                    break;
                case 2:
                    $rolTexto = 'Asistente';
                    break;
                case 3:
                    $rolTexto = 'Administrador';
                    break;
                case 4:
                    $rolTexto = 'Docente';
                    break;
                case 5:
                    $rolTexto = 'Desactivado';
                    break;
                default:
                    $rolTexto = 'Desconocido';
                    break;
            }
            
            // Agregar el rol como texto al array
            $row['rol_texto'] = $rolTexto;
            
            // Si no hay email, mostrar "Sin email"
            if (empty($row['email'])) {
                $row['email'] = 'Sin email';
            }
            
            // Si no hay password (roles que no la requieren), mostrar "Sin contraseña"
            if (empty($row['password'])) {
                $row['password'] = 'Sin contraseña';
            }
            
            $usuarios[] = $row;
        }
        return $usuarios;
    } else {
        return array(); // Retornar array vacío si no hay usuarios
    }
}

    public function eliminarUsuario($link, $id) {
    // Verificar que el ID no esté vacío
    if (empty($id)) {
        return false;
    }
    
    // Verificar que el usuario existe antes de "eliminarlo"
    $queryCheck = "SELECT id FROM Usuarios WHERE id = ? AND rol != 5";
    $stmtCheck = mysqli_prepare($link, $queryCheck);
    mysqli_stmt_bind_param($stmtCheck, "i", $id);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    
    if (mysqli_num_rows($resultCheck) === 0) {
        return false; // Usuario no existe o ya está desactivado
    }
    
    // Cambiar el rol a 5 para "eliminar" (desactivar) el usuario
    $query = "UPDATE Usuarios SET rol = 5 WHERE id = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        return true; // Usuario desactivado exitosamente
    } else {
        return false; // Error al desactivar usuario
    }
}

    public function editarUsuario($link, $id, $nombre, $email, $ci, $rol, $password = null) {
    // Verificar que los campos obligatorios no estén vacíos
    if (empty($id) || empty($nombre) || empty($ci) || empty($rol)) {
        return false;
    }
    
    // Verificar que el usuario existe
    $queryCheck = "SELECT id FROM Usuarios WHERE id = ?";
    $stmtCheck = mysqli_prepare($link, $queryCheck);
    mysqli_stmt_bind_param($stmtCheck, "i", $id);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    
    if (mysqli_num_rows($resultCheck) === 0) {
        return false; // Usuario no existe
    }
    
    // Verificar que el email no esté ya registrado por otro usuario (solo si se proporciona)
    if (!empty($email)) {
        $queryCheckEmail = "SELECT id FROM Usuarios WHERE email = ? AND id != ?";
        $stmtCheckEmail = mysqli_prepare($link, $queryCheckEmail);
        mysqli_stmt_bind_param($stmtCheckEmail, "si", $email, $id);
        mysqli_stmt_execute($stmtCheckEmail);
        $resultCheckEmail = mysqli_stmt_get_result($stmtCheckEmail);
        
        if (mysqli_num_rows($resultCheckEmail) > 0) {
            return false; // Email ya existe en otro usuario
        }
    }
    
    // Verificar que la cédula no esté ya registrada por otro usuario
    $queryCheckCI = "SELECT id FROM Usuarios WHERE ci = ? AND id != ?";
    $stmtCheckCI = mysqli_prepare($link, $queryCheckCI);
    mysqli_stmt_bind_param($stmtCheckCI, "si", $ci, $id);
    mysqli_stmt_execute($stmtCheckCI);
    $resultCheckCI = mysqli_stmt_get_result($stmtCheckCI);
    
    if (mysqli_num_rows($resultCheckCI) > 0) {
        return false; // Cédula ya existe en otro usuario
    }
    
    // Preparar la consulta de actualización
    if ($rol == 2 || $rol == 3) {
        // Para Asistente (rol 2) o Administrador (rol 3) se requiere contraseña
        if (empty($password)) {
            return false;
        }
        if (!empty($email)) {
            $query = "UPDATE Usuarios SET nombre = ?, email = ?, password = ?, ci = ?, rol = ? WHERE id = ?";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "ssssii", $nombre, $email, $password, $ci, $rol, $id);
        } else {
            $query = "UPDATE Usuarios SET nombre = ?, email = NULL, password = ?, ci = ?, rol = ? WHERE id = ?";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "sssii", $nombre, $password, $ci, $rol, $id);
        }
    } else {
        // Para Estudiante (rol 1), Docente (rol 4) o Desactivado (rol 5) no se requiere contraseña
        if (!empty($email)) {
            $query = "UPDATE Usuarios SET nombre = ?, email = ?, password = NULL, ci = ?, rol = ? WHERE id = ?";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "sssii", $nombre, $email, $ci, $rol, $id);
        } else {
            $query = "UPDATE Usuarios SET nombre = ?, email = NULL, password = NULL, ci = ?, rol = ? WHERE id = ?";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "ssii", $nombre, $ci, $rol, $id);
        }
    }
    
    // Ejecutar la actualización
    if (mysqli_stmt_execute($stmt)) {
        return true; // Usuario actualizado exitosamente
    } else {
        return false; // Error al actualizar usuario
    }
}
    
    public function login($email, $password) {
        // Implementar lógica de login
        return $this->email === $email && $this->password === $password;
    }
    
    public function listarUsuario() {
        // Implementar lógica para listar usuarios
    }
    
    // Getters y Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }
    public function getPassword() { return $this->password; }
    public function setPassword($password) { $this->password = $password; }
    public function getRol() { return $this->rol; }
    public function setRol($rol) { $this->rol = $rol; }
    public function getCi() { return $this->ci; }
    public function setCi($ci) { $this->ci = $ci; }
    public function getIdTipoUsuario() { return $this->idTipoUsuario; }
    public function setIdTipoUsuario($idTipoUsuario) { $this->idTipoUsuario = $idTipoUsuario; }
}
?>
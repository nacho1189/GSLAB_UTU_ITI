<?php
// Incluir la configuración y el modelo
require_once '../config/config.php';
require_once '../modelos/Usuarios.php';

// Verificar que se recibió una petición POST o GET
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verificar qué acción se quiere realizar
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        
        switch ($accion) {
            case 'loginUser':
                iniciarSesionUsuarioController();
                break;
            case 'loginAdmin':
                iniciarSesionAdminController();
                break;
            case 'loginAdminReal':
                iniciarSesionAdminRealController();
                break;
            case 'loginDocente':
                iniciariSesionDocenteController();
                break;
            case 'altaUsuario':
                altaUsuarioControlador();
                break;
            case 'editarUsuario':
                editarUsuarioControlador();
                break;
            default:
                echo "Acción no válida";
                break;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    // Manejar peticiones GET (para consultas)
    if (isset($_GET['accion'])) {
        $accion = $_GET['accion'];
        
        switch ($accion) {
            case 'listar':
                consultaUsuariosControlador();
                break;
            case 'editar':
                editarUsuarioControlador();
                break;
            case 'eliminar':
                eliminarUsuarioControlador();
                break;
            case 'obtenerUsuario':
                obtenerUsuarioControlador();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción GET no válida']);
                break;
        }
    }
}

function iniciarSesionUsuarioController() {
    global $link;

    $ci = $_POST['ci'];
    $usuario = new Usuarios();
    $resultado = $usuario->iniciarSesionUsuario($link, $ci);
    
    if ($resultado === true) {
        header("Location: ../vistas/EstadoPc.html");
        exit();
    } else {
        header("Location: ../vistas/Usuario.html?error=login_failed");
        exit();
    }
}

function iniciariSesionDocenteController() {
    global $link;

    $ci = $_POST['ci'];
    $usuario = new Usuarios();
    $resultado = $usuario->iniciarSesionDocente($link, $ci);
    
    if ($resultado === true) {
        header("Location: ../vistas/ConsultaPCDocente.html");
        exit();
    } else {
        header("Location: ../vistas/Docente.html?error=login_failed");
        exit();
    }
}

function iniciarSesionAdminController() {
    global $link;

    $ci = $_POST['ci'];
    $password = $_POST['password'];
    $usuario = new Usuarios();
    $resultado = $usuario->iniciarSesionAdmin($link, $ci, $password);
    
    if ($resultado === true) {
        header("Location: ../vistas/ConsultaPc.html");
        exit();
    } else {
        header("Location: ../vistas/Admin.html?error=login_failed");
        exit();
    }
}

function iniciarSesionAdminRealController() {
    global $link;

    $ci = $_POST['ci'];
    $password = $_POST['password'];
    $usuario = new Usuarios();
    $resultado = $usuario->iniciarSesionAdminReal($link, $ci, $password);
    
    if ($resultado === true) {
        header("Location: ../vistas/IndexAdmin.html");
        exit();
    } else {
        header("Location: ../vistas/AdminReal.html?error=login_failed");
        exit();
    }
}

function altaUsuarioControlador() {
    global $link;

    // Recibir los datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $ci = $_POST['cedula'] ?? '';
    $rol = $_POST['rol'] ?? '';
    
    // Validaciones básicas (email ya no es obligatorio)
    if (empty($nombre) || empty($ci) || empty($rol)) {
        header("Location: ../vistas/AltaUsuario.html?error=campos_vacios");
        exit();
    }
    
    // Crear una instancia del modelo Usuario
    $usuario = new Usuarios();

    // Pasar los datos al modelo incluyendo la conexión $link
    $resultado = $usuario->altaUsuario($link, $nombre, $email, $password, $ci, $rol);
    
    if ($resultado === true) {
        header("Location: ../vistas/ExitoAltaUsuario.html?success=usuario_registrado");
        exit();
    } else {
        header("Location: ../vistas/AltaUsuario.html?error=registro_failed");
        exit();
    }
}

function consultaUsuariosControlador() {
    global $link;
    
    // Establecer el header para JSON
    header('Content-Type: application/json');
    
    // Crear una instancia del modelo Usuario
    $usuario = new Usuarios();
    
    // Obtener la lista de usuarios
    $usuarios = $usuario->consultaUsuarios($link);
    
    if ($usuarios !== false) {
        // Devolver respuesta exitosa con los usuarios
        echo json_encode([
            'success' => true,
            'usuarios' => $usuarios
        ]);
    } else {
        // Devolver respuesta de error
        echo json_encode([
            'success' => false,
            'message' => 'Error al consultar usuarios'
        ]);
    }
}

function eliminarUsuarioControlador() {
    global $link;
    
    // Establecer el header para JSON
    header('Content-Type: application/json');
    
    // Verificar que se recibió el ID del usuario
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de usuario no proporcionado'
        ]);
        return;
    }
    
    $id = $_GET['id'];
    
    // Crear una instancia del modelo Usuario
    $usuario = new Usuarios();
    
    // Eliminar (desactivar) el usuario
    $resultado = $usuario->eliminarUsuario($link, $id);
    
    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario desactivado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al desactivar usuario'
        ]);
    }
}

function obtenerUsuarioControlador() {
    global $link;
    
    // Establecer el header para JSON
    header('Content-Type: application/json');
    
    // Verificar que se recibió el ID del usuario
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de usuario no proporcionado'
        ]);
        return;
    }
    
    $id = $_GET['id'];
    
    // Consulta para obtener el usuario específico
    $query = "SELECT id, nombre, email, password, ci, rol FROM Usuarios WHERE id = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);
        
        // Si no hay email, enviar string vacío
        if (empty($usuario['email'])) {
            $usuario['email'] = '';
        }
        
        // Si no hay password, enviar string vacío
        if (empty($usuario['password'])) {
            $usuario['password'] = '';
        }
        
        echo json_encode([
            'success' => true,
            'usuario' => $usuario
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
    }
}

function editarUsuarioControlador() {
    global $link;
    
    // Establecer el header para JSON
    header('Content-Type: application/json');
    
    // Recibir los datos del formulario
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $ci = $_POST['ci'] ?? '';
    $rol = $_POST['rol'] ?? '';
    
    // Validaciones básicas
    if (empty($id) || empty($nombre) || empty($ci) || empty($rol)) {
        echo json_encode([
            'success' => false,
            'message' => 'Campos obligatorios faltantes'
        ]);
        return;
    }
    
    // Crear una instancia del modelo Usuario
    $usuario = new Usuarios();
    
    // Editar el usuario
    $resultado = $usuario->editarUsuario($link, $id, $nombre, $email, $ci, $rol, $password);
    
    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar usuario. Verifique que el email y cédula no estén duplicados.'
        ]);
    }
}
?>
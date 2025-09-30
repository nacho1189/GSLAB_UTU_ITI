<?php
session_start();
require_once '../config/config.php';

// Verificar que el archivo de la clase exista
$registrosPath = '../modelos/Registros.php';
if (!file_exists($registrosPath)) {
    die("Error: No se puede encontrar el archivo Registros.php en: " . $registrosPath);
}

require_once $registrosPath;

// Verificar que la clase se haya cargado correctamente
if (!class_exists('Registros')) {
    die("Error: La clase Registros no se pudo cargar correctamente");
}

// MANEJAR REQUESTS GET PARA CONSULTAS
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    
    switch ($accion) {
        case 'consultarEstados':
            consultarEstadosController();
            break;
        case 'consultarPC':
            consultarPCController();
            break;
        default:
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'success' => false,
                'error' => 'Acción no válida: ' . $accion
            ), JSON_UNESCAPED_UNICODE);
            exit();
    }
}

// MANEJAR REQUESTS POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        
        switch ($accion) {
            case 'registroUsuario':
                registroUsuarioController();
                break;
            case 'consultarEstados':
                consultarEstadosController();
                break;
            default:
                redirigirError("Acción no válida: " . $accion);
                break;
        }
    } else {
        redirigirError("No se especificó una acción válida");
    }
}

/**
 * Controlador para consultar los detalles de una PC específica
 */
function consultarPCController() {
    global $link;
    
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        error_log("=== CONSULTA PC ESPECÍFICA ===");
        
        // Obtener parámetros
        $pc = $_GET['pc'] ?? '';
        $lab = $_GET['lab'] ?? 'LAB6';
        
        if (empty($pc)) {
            throw new Exception("No se especificó qué PC consultar");
        }
        
        // Construir hostNamePC
        $hostNamePC = $lab . "-" . $pc;
        
        error_log("Consultando PC: $hostNamePC");
        
        // Llamar al método estático de la clase Registros
        $registro = Registros::obtenerUltimoRegistroPC($link, $hostNamePC);
        
        if (!$registro) {
            throw new Exception("No se encontraron datos para la PC: $pc");
        }
        
        error_log("Registro encontrado: " . print_r($registro, true));
        
        // Preparar respuesta
        $respuesta = array(
            'success' => true,
            'pc' => $pc,
            'laboratorio' => $lab,
            'fecha_consulta' => date('Y-m-d H:i:s'),
            'registro' => $registro
        );
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("ERROR en consultarPCController: " . $e->getMessage());
        
        $respuesta = array(
            'success' => false,
            'error' => $e->getMessage(),
            'fecha_consulta' => date('Y-m-d H:i:s')
        );
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }
    
    exit();
}

/**
 * Controlador para consultar los estados actuales de todas las PCs
 */
function consultarEstadosController() {
    global $link;
    
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        error_log("=== CONSULTA ESTADOS PCS ===");
        
        // Obtener el laboratorio de los parámetros (por defecto LAB6)
        $laboratorio = $_GET['lab'] ?? $_POST['lab'] ?? 'LAB6';
        
        error_log("Consultando estados para laboratorio: $laboratorio");
        
        // Llamar al método estático de la clase Registros
        $registros = Registros::obtenerUltimosRegistrosPorLab($link, $laboratorio);
        
        error_log("Registros encontrados: " . count($registros));
        error_log("Datos: " . print_r($registros, true));
        
        // Preparar respuesta en formato JSON
        $respuesta = array(
            'success' => true,
            'laboratorio' => $laboratorio,
            'fecha_consulta' => date('Y-m-d H:i:s'),
            'registros' => $registros,
            'total_pcs' => count($registros)
        );
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("ERROR en consultarEstadosController: " . $e->getMessage());
        
        $respuesta = array(
            'success' => false,
            'error' => $e->getMessage(),
            'fecha_consulta' => date('Y-m-d H:i:s')
        );
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    }
    
    exit();
}

function registroUsuarioController() {
    global $link;
    
    // Debug completo
    error_log("=== INICIO REGISTRO USUARIO ===");
    error_log("POST: " . print_r($_POST, true));
    error_log("SESSION: " . print_r($_SESSION, true));
    
    // 1. VERIFICAR SESIÓN - obtener idUsuario de la sesión
    $idUsuario = null;
    if (isset($_SESSION['usuario_id'])) {
        $idUsuario = $_SESSION['usuario_id'];
    } elseif (isset($_SESSION['id'])) {
        $idUsuario = $_SESSION['id'];
    }
    
    if (!$idUsuario) {
        error_log("ERROR: Sin sesión activa");
        redirigirError("Sin sesión activa. Variables disponibles: " . implode(", ", array_keys($_SESSION ?? [])));
        return;
    }
    
    // 2. OBTENER DATOS DEL FORMULARIO
    $pcNumber = $_POST['pcNumber'] ?? '';
    $estado = $_POST['estado'] ?? '1';
    $comentario = $_POST['comentario'] ?? '';
    
    error_log("Datos extraídos:");
    error_log("- PC: '$pcNumber'");
    error_log("- Estado: '$estado'");
    error_log("- Comentario: '$comentario'");
    error_log("- ID Usuario: '$idUsuario'");
    
    // 3. VALIDACIONES SIMPLES
    if (empty($pcNumber)) {
        error_log("ERROR: PC vacío");
        redirigirError("PC no especificada");
        return;
    }
    
    if (empty($comentario)) {
        error_log("ERROR: Comentario vacío");
        redirigirError("Comentario obligatorio");
        return;
    }
    
    // 4. INSERTAR EN BD usando la nueva estructura
    try {
        // Usar los campos: fecha, descripcion, estado, diskFree, idUsuario, hostNamePC, idLab
        $query = "INSERT INTO Registros (fecha, descripcion, estado, diskFree, idUsuario, hostNamePC, idLab) VALUES (NOW(), ?, ?, NULL, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $query);
        
        if (!$stmt) {
            error_log("ERROR preparando query: " . mysqli_error($link));
            redirigirError("Error de BD: " . mysqli_error($link));
            return;
        }
        
        $estadoInt = (int)$estado;
        $idLab = 6; // Hardcodeado como laboratorio 6
        
        // Crear hostNamePC con formato "LAB6-PCX"
        $hostNamePC = "LAB6-" . $pcNumber;
        
        error_log("hostNamePC generado: '$hostNamePC'");
        error_log("idLab: $idLab");
        
        mysqli_stmt_bind_param($stmt, "siisi", $comentario, $estadoInt, $idUsuario, $hostNamePC, $idLab);
        
        if (mysqli_stmt_execute($stmt)) {
            error_log("✅ INSERT exitoso");
            mysqli_stmt_close($stmt);
            
            // Redirigir con éxito
            redirigirExito($pcNumber, $estadoInt, $comentario, $idUsuario);
        } else {
            error_log("ERROR ejecutando query: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            redirigirError("Error ejecutando consulta: " . mysqli_stmt_error($stmt));
        }
        
    } catch (Exception $e) {
        error_log("EXCEPCIÓN: " . $e->getMessage());
        redirigirError("Error: " . $e->getMessage());
    }
}

function redirigirExito($numeroPc, $estado, $comentario, $idUsuario) {
    // Opción 1: Redirigir a la página de éxito (como está ahora)
    $fechaActual = date('d/m/Y H:i:s');
    $estadoTexto = $estado ? 'Funcionando' : 'No Funcionando';
    
    $params = array(
        'pc' => urlencode($numeroPc),
        'estado' => urlencode($estadoTexto),
        'comentario' => urlencode($comentario),
        'usuario' => urlencode($idUsuario),
        'fecha' => urlencode($fechaActual),
        'volver' => urlencode("ConsultaPcDetallada.html?pc=" . $numeroPc . "&lab=LAB6")
    );
    
    $queryString = http_build_query($params);
    $url = "../vistas/exito.html?" . $queryString;
    
    // Opción 2: Si prefieres redirigir directamente a los detalles de la PC
    // $url = "../vistas/ConsultaPcDetallada.html?pc=" . urlencode($numeroPc) . "&lab=LAB6&registro=exitoso";
    
    header("Location: " . $url);
    exit();
}

function redirigirError($mensaje) {
    $url = "../vistas/error.html?mensaje=" . urlencode($mensaje);
    header("Location: " . $url);
    exit();
}
?>
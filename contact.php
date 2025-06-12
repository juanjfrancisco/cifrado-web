<?php
session_start();

// Configuración
$database_file = 'data/contacts.db';
$data_dir = 'data';

// Crear directorio de datos si no existe
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
}

// Crear base de datos SQLite si no existe
if (!file_exists($database_file)) {
    $db = new SQLite3($database_file);
    $db->exec('
        CREATE TABLE IF NOT EXISTS contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            email TEXT NOT NULL,
            empresa TEXT,
            telefono TEXT,
            mensaje TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address TEXT,
            user_agent TEXT
        )
    ');
    $db->close();
}

// Función para enviar respuesta JSON
function jsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Función para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para limpiar datos de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Solo permitir métodos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Método no permitido');
}

// Verificar que sea una petición AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    jsonResponse('error', 'Solo se permiten peticiones AJAX');
}

// Validar y limpiar datos
$nombre = cleanInput($_POST['nombre'] ?? '');
$email = cleanInput($_POST['email'] ?? '');
$empresa = cleanInput($_POST['empresa'] ?? '');
$telefono = cleanInput($_POST['telefono'] ?? '');
$mensaje = cleanInput($_POST['mensaje'] ?? '');

// Validaciones
$errors = [];

if (empty($nombre)) {
    $errors[] = 'El nombre es requerido';
}

if (empty($email)) {
    $errors[] = 'El email es requerido';
} elseif (!isValidEmail($email)) {
    $errors[] = 'El email no es válido';
}

if (empty($mensaje)) {
    $errors[] = 'El mensaje es requerido';
}

if (!empty($errors)) {
    jsonResponse('error', 'Datos inválidos', $errors);
}

// Protección contra spam - límite de envíos por IP
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

try {
    $db = new SQLite3($database_file);
    
    // Verificar envíos recientes de la misma IP (últimos 10 minutos)
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM contacts WHERE ip_address = ? AND created_at > datetime("now", "-10 minutes")');
    $stmt->bindValue(1, $ip_address, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray();
    
    if ($row['count'] >= 3) {
        jsonResponse('error', 'Demasiados envíos recientes. Por favor, espera unos minutos.');
    }
    
    // Insertar nuevo contacto
    $stmt = $db->prepare('
        INSERT INTO contacts (nombre, email, empresa, telefono, mensaje, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->bindValue(1, $nombre, SQLITE3_TEXT);
    $stmt->bindValue(2, $email, SQLITE3_TEXT);
    $stmt->bindValue(3, $empresa, SQLITE3_TEXT);
    $stmt->bindValue(4, $telefono, SQLITE3_TEXT);
    $stmt->bindValue(5, $mensaje, SQLITE3_TEXT);
    $stmt->bindValue(6, $ip_address, SQLITE3_TEXT);
    $stmt->bindValue(7, $user_agent, SQLITE3_TEXT);
    
    $result = $stmt->execute();
    
    if ($result) {
        $contact_id = $db->lastInsertRowID();
        
        // Enviar email de notificación (usando mail() simple)
        $to = 'info@cifrado.com';
        $subject = 'Nuevo contacto desde el sitio web - ' . $nombre;
        $email_body = "
Nuevo contacto recibido:

Nombre: $nombre
Email: $email
Empresa: $empresa
Teléfono: $telefono

Mensaje:
$mensaje

---
IP: $ip_address
Fecha: " . date('Y-m-d H:i:s') . "
ID: $contact_id
        ";
        
        $headers = "From: noreply@cifrado.com\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Intentar enviar email (puede fallar si no hay servidor SMTP configurado)
        $email_sent = @mail($to, $subject, $email_body, $headers);
        
        jsonResponse('success', 'Mensaje enviado correctamente. Te contactaremos pronto.', [
            'id' => $contact_id,
            'email_sent' => $email_sent
        ]);
        
    } else {
        jsonResponse('error', 'Error al guardar el mensaje. Inténtalo nuevamente.');
    }
    
} catch (Exception $e) {
    error_log('Error en contact.php: ' . $e->getMessage());
    jsonResponse('error', 'Error interno del servidor');
} finally {
    if (isset($db)) {
        $db->close();
    }
}
?>
<?php
// Script de inicialización de la base de datos
$database_file = '/var/www/html/data/contacts.db';
$data_dir = '/var/www/html/data';

// Crear directorio si no existe
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
    echo "Directorio data creado\n";
}

// Verificar permisos del directorio
if (!is_writable($data_dir)) {
    chmod($data_dir, 0755);
    echo "Permisos del directorio corregidos\n";
}

try {
    // Crear/abrir base de datos
    $db = new SQLite3($database_file);
    echo "Base de datos conectada exitosamente\n";
    
    // Crear tabla si no existe
    $sql = '
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
    ';
    
    $result = $db->exec($sql);
    if ($result) {
        echo "Tabla contacts creada/verificada exitosamente\n";
    } else {
        echo "Error al crear tabla: " . $db->lastErrorMsg() . "\n";
    }
    
    // Verificar estructura de la tabla
    $result = $db->query("PRAGMA table_info(contacts)");
    echo "Estructura de la tabla contacts:\n";
    while ($row = $result->fetchArray()) {
        echo "- {$row['name']} ({$row['type']})\n";
    }
    
    // Insertar datos de prueba (opcional)
    $test_contact = $db->prepare('
        INSERT INTO contacts (nombre, email, empresa, mensaje, ip_address) 
        VALUES (?, ?, ?, ?, ?)
    ');
    $test_contact->bindValue(1, 'Test Usuario', SQLITE3_TEXT);
    $test_contact->bindValue(2, 'test@ejemplo.com', SQLITE3_TEXT);
    $test_contact->bindValue(3, 'Empresa Test', SQLITE3_TEXT);
    $test_contact->bindValue(4, 'Este es un mensaje de prueba', SQLITE3_TEXT);
    $test_contact->bindValue(5, '127.0.0.1', SQLITE3_TEXT);
    
    if ($test_contact->execute()) {
        echo "Contacto de prueba insertado\n";
    }
    
    // Verificar datos
    $count_result = $db->query('SELECT COUNT(*) as total FROM contacts');
    $count_row = $count_result->fetchArray();
    echo "Total de contactos en la BD: " . $count_row['total'] . "\n";
    
    $db->close();
    
    // Establecer permisos del archivo de BD
    chmod($database_file, 0664);
    echo "Permisos del archivo de BD establecidos\n";
    
    echo "\n✅ Inicialización completada exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la inicialización: " . $e->getMessage() . "\n";
    echo "Información del sistema:\n";
    echo "- PHP version: " . PHP_VERSION . "\n";
    echo "- SQLite3 extension: " . (extension_loaded('sqlite3') ? 'Cargada' : 'NO CARGADA') . "\n";
    echo "- Directorio data existe: " . (is_dir($data_dir) ? 'Sí' : 'No') . "\n";
    echo "- Directorio data escribible: " . (is_writable($data_dir) ? 'Sí' : 'No') . "\n";
    echo "- Archivo BD existe: " . (file_exists($database_file) ? 'Sí' : 'No') . "\n";
    if (file_exists($database_file)) {
        echo "- Archivo BD escribible: " . (is_writable($database_file) ? 'Sí' : 'No') . "\n";
    }
}
?>
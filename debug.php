<?php
// Script de diagnóstico
echo "<h2>Diagnóstico del Sistema CifraDO</h2>";
echo "<pre>";

echo "=== INFORMACIÓN DEL SISTEMA ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Documento Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script actual: " . __FILE__ . "\n";
echo "Usuario PHP: " . get_current_user() . " (UID: " . getmyuid() . ")\n";

echo "\n=== EXTENSIONES PHP ===\n";
echo "SQLite3: " . (extension_loaded('sqlite3') ? '✅ Cargada' : '❌ NO CARGADA') . "\n";
echo "PDO: " . (extension_loaded('pdo') ? '✅ Cargada' : '❌ NO CARGADA') . "\n";
echo "PDO SQLite: " . (extension_loaded('pdo_sqlite') ? '✅ Cargada' : '❌ NO CARGADA') . "\n";

$data_dir = '/var/www/html/data';
$database_file = '/var/www/html/data/contacts.db';

echo "\n=== DIRECTORIO DATA ===\n";
echo "Ruta: $data_dir\n";
echo "Existe: " . (is_dir($data_dir) ? '✅ Sí' : '❌ No') . "\n";
if (is_dir($data_dir)) {
    echo "Permisos: " . substr(sprintf('%o', fileperms($data_dir)), -4) . "\n";
    echo "Propietario: " . fileowner($data_dir) . "\n";
    echo "Grupo: " . filegroup($data_dir) . "\n";
    echo "Escribible: " . (is_writable($data_dir) ? '✅ Sí' : '❌ No') . "\n";
    echo "Legible: " . (is_readable($data_dir) ? '✅ Sí' : '❌ No') . "\n";
}

echo "\n=== ARCHIVO BASE DE DATOS ===\n";
echo "Ruta: $database_file\n";
echo "Existe: " . (file_exists($database_file) ? '✅ Sí' : '❌ No') . "\n";
if (file_exists($database_file)) {
    echo "Tamaño: " . filesize($database_file) . " bytes\n";
    echo "Permisos: " . substr(sprintf('%o', fileperms($database_file)), -4) . "\n";
    echo "Propietario: " . fileowner($database_file) . "\n";
    echo "Grupo: " . filegroup($database_file) . "\n";
    echo "Escribible: " . (is_writable($database_file) ? '✅ Sí' : '❌ No') . "\n";
    echo "Legible: " . (is_readable($database_file) ? '✅ Sí' : '❌ No') . "\n";
}

echo "\n=== PRUEBA DE CONEXIÓN ===\n";
try {
    if (!file_exists($database_file)) {
        echo "Intentando crear base de datos...\n";
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0775, true);
            echo "Directorio creado\n";
        }
    }
    
    $db = new SQLite3($database_file);
    echo "✅ Conexión SQLite3 exitosa\n";
    
    // Verificar tabla
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='contacts'");
    if ($result && $row = $result->fetchArray()) {
        echo "✅ Tabla 'contacts' existe\n";
        
        // Contar registros
        $count_result = $db->query('SELECT COUNT(*) as total FROM contacts');
        if ($count_result && $count_row = $count_result->fetchArray()) {
            echo "📊 Total de contactos: " . $count_row['total'] . "\n";
        }
    } else {
        echo "⚠️ Tabla 'contacts' no existe, creando...\n";
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
        if ($db->exec($sql)) {
            echo "✅ Tabla 'contacts' creada exitosamente\n";
        } else {
            echo "❌ Error al crear tabla: " . $db->lastErrorMsg() . "\n";
        }
    }
    
    $db->close();
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "Código de error: " . $e->getCode() . "\n";
}

echo "\n=== VARIABLES DE ENTORNO ===\n";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'PATH') !== false || strpos($key, 'USER') !== false || strpos($key, 'HOME') !== false) {
        echo "$key: $value\n";
    }
}

echo "\n=== PRUEBAS ADICIONALES ===\n";
// Probar escritura
$test_file = $data_dir . '/test_write.txt';
if (is_dir($data_dir)) {
    if (file_put_contents($test_file, 'test') !== false) {
        echo "✅ Escritura en directorio data: OK\n";
        unlink($test_file);
    } else {
        echo "❌ No se puede escribir en directorio data\n";
    }
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
echo "</pre>";
?>
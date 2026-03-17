<?php
// Script para corregir permisos
echo "🔧 Corrigiendo permisos del sistema...\n";

$data_dir = '/var/www/html/data';
$database_file = '/var/www/html/data/contacts.db';

// Función para ejecutar comandos del sistema
function runCommand($command) {
    $output = [];
    $return_var = 0;
    exec($command . " 2>&1", $output, $return_var);
    return ['output' => implode("\n", $output), 'success' => $return_var === 0];
}

echo "📁 Verificando directorio data...\n";

// Crear directorio si no existe
if (!is_dir($data_dir)) {
    if (mkdir($data_dir, 0775, true)) {
        echo "✅ Directorio creado: $data_dir\n";
    } else {
        echo "❌ Error al crear directorio: $data_dir\n";
        exit(1);
    }
}

// Corregir propietario y permisos del directorio
$chown_result = runCommand("chown -R www-data:www-data $data_dir");
if ($chown_result['success']) {
    echo "✅ Propietario cambiado a www-data\n";
} else {
    echo "⚠️ No se pudo cambiar propietario: " . $chown_result['output'] . "\n";
}

$chmod_result = runCommand("chmod -R 775 $data_dir");
if ($chmod_result['success']) {
    echo "✅ Permisos establecidos a 775\n";
} else {
    echo "⚠️ No se pudieron cambiar permisos: " . $chmod_result['output'] . "\n";
}

// Verificar estado final
echo "\n📊 Estado final:\n";
echo "Directorio existe: " . (is_dir($data_dir) ? "✅" : "❌") . "\n";
echo "Directorio escribible: " . (is_writable($data_dir) ? "✅" : "❌") . "\n";
echo "Directorio legible: " . (is_readable($data_dir) ? "✅" : "❌") . "\n";

// Información detallada de permisos
if (is_dir($data_dir)) {
    $perms = fileperms($data_dir);
    echo "Permisos octales: " . substr(sprintf('%o', $perms), -4) . "\n";
    echo "Propietario UID: " . fileowner($data_dir) . "\n";
    echo "Grupo GID: " . filegroup($data_dir) . "\n";
}

// Crear base de datos si no existe
if (!file_exists($database_file)) {
    echo "\n💾 Creando base de datos...\n";
    try {
        $db = new SQLite3($database_file);
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
            echo "✅ Base de datos y tabla creadas\n";
        } else {
            echo "❌ Error al crear tabla: " . $db->lastErrorMsg() . "\n";
        }
        $db->close();
        
        // Corregir permisos del archivo de BD
        chmod($database_file, 0664);
        echo "✅ Permisos del archivo de BD establecidos\n";
        
    } catch (Exception $e) {
        echo "❌ Error al crear BD: " . $e->getMessage() . "\n";
    }
}

// Prueba de escritura
echo "\n🧪 Prueba de escritura...\n";
$test_file = $data_dir . '/test_write.txt';
if (file_put_contents($test_file, 'test de escritura ' . date('Y-m-d H:i:s')) !== false) {
    echo "✅ Escritura exitosa\n";
    unlink($test_file);
    echo "✅ Archivo de prueba eliminado\n";
} else {
    echo "❌ Fallo en escritura\n";
    echo "Error: " . error_get_last()['message'] . "\n";
}

echo "\n🎉 Corrección de permisos completada!\n";
?>
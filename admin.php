<?php
session_start();

// Configuración simple de autenticación
$admin_user = 'admin';
$admin_pass = 'cifrado2025!'; // Cambiar en producción
$database_file = 'data/contacts.db';

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
        $_POST['username'] === $admin_user && 
        $_POST['password'] === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

function showLoginForm() {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - CifraDO</title>
        <style>
            body { 
                font-family: 'Lato', sans-serif; 
                background: linear-gradient(135deg, #0f172a, #1e3a8a); 
                height: 100vh; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                margin: 0; 
            }
            .login-form { 
                background: white; 
                padding: 2rem; 
                border-radius: 1rem; 
                box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
                width: 300px; 
            }
            .login-form h2 { 
                color: #0f172a; 
                margin-bottom: 1.5rem; 
                text-align: center; 
            }
            .form-group { 
                margin-bottom: 1rem; 
            }
            label { 
                display: block; 
                margin-bottom: 0.5rem; 
                color: #374151; 
                font-weight: 600; 
            }
            input { 
                width: 100%; 
                padding: 0.75rem; 
                border: 1px solid #d1d5db; 
                border-radius: 0.5rem; 
                font-size: 1rem; 
                box-sizing: border-box; 
            }
            input:focus { 
                outline: none; 
                border-color: #3b82f6; 
                box-shadow: 0 0 0 3px rgba(59,130,246,0.1); 
            }
            .btn { 
                width: 100%; 
                padding: 0.75rem; 
                background: #3b82f6; 
                color: white; 
                border: none; 
                border-radius: 0.5rem; 
                font-size: 1rem; 
                cursor: pointer; 
                font-weight: 600; 
            }
            .btn:hover { 
                background: #2563eb; 
            }
            .error { 
                color: #dc2626; 
                font-size: 0.875rem; 
                margin-top: 0.5rem; 
            }
        </style>
    </head>
    <body>
        <form method="POST" class="login-form">
            <h2>Acceso Admin</h2>
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Ingresar</button>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="error">Credenciales incorrectas</div>
            <?php endif; ?>
        </form>
    </body>
    </html>
    <?php
}

// Verificar que existe la base de datos
if (!file_exists($database_file)) {
    die('Base de datos no encontrada');
}

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

try {
    $db = new SQLite3($database_file);
    
    // Contar total de contactos
    $count_result = $db->query('SELECT COUNT(*) as total FROM contacts');
    $count_row = $count_result->fetchArray();
    $total_contacts = $count_row['total'];
    $total_pages = ceil($total_contacts / $per_page);
    
    // Obtener contactos de la página actual
    $stmt = $db->prepare('
        SELECT * FROM contacts 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ');
    $stmt->bindValue(1, $per_page, SQLITE3_INTEGER);
    $stmt->bindValue(2, $offset, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $contacts = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $contacts[] = $row;
    }
    
} catch (Exception $e) {
    die('Error al acceder a la base de datos: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Admin - Contactos CifraDO</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3b82f6;
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
        }
        .contacts-table {
            background: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        tr:hover {
            background: #f9fafb;
        }
        .pagination {
            margin-top: 2rem;
            text-align: center;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            text-decoration: none;
            border: 1px solid #d1d5db;
            border-radius: 0.25rem;
        }
        .pagination a:hover {
            background: #f3f4f6;
        }
        .pagination .current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        .btn {
            padding: 0.5rem 1rem;
            background: #6b7280;
            color: white;
            text-decoration: none;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .btn:hover {
            background: #4b5563;
        }
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            table {
                font-size: 0.875rem;
            }
            th, td {
                padding: 0.5rem;
            }
            .message-preview {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Panel de Administración - CifraDO</h1>
            <a href="?logout=1" class="btn">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_contacts; ?></div>
                <div class="stat-label">Total Contactos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $today_result = $db->query("SELECT COUNT(*) as today FROM contacts WHERE date(created_at) = date('now')");
                    $today_row = $today_result->fetchArray();
                    echo $today_row['today'];
                    ?>
                </div>
                <div class="stat-label">Contactos Hoy</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $week_result = $db->query("SELECT COUNT(*) as week FROM contacts WHERE created_at >= date('now', '-7 days')");
                    $week_row = $week_result->fetchArray();
                    echo $week_row['week'];
                    ?>
                </div>
                <div class="stat-label">Esta Semana</div>
            </div>
        </div>

        <div class="contacts-table">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Empresa</th>
                        <th>Teléfono</th>
                        <th>Mensaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($contact['nombre']); ?></td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                                <?php echo htmlspecialchars($contact['email']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($contact['empresa'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($contact['telefono'] ?: '-'); ?></td>
                        <td class="message-preview" title="<?php echo htmlspecialchars($contact['mensaje']); ?>">
                            <?php echo htmlspecialchars($contact['mensaje']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">&laquo; Anterior</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$db->close();
?>
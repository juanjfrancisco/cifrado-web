# CifraDO Landing Page

Landing page moderna para CifraDO con formulario de contacto funcional, optimización SEO y panel de administración.

## Características

### ✨ Nuevas Funcionalidades
- **Formulario de contacto funcional** con validación AJAX
- **Base de datos SQLite** para almacenar contactos
- **Panel de administración** simple para ver contactos
- **SEO optimizado** con meta tags, Open Graph y structured data
- **Imágenes locales** optimizadas (SVG)
- **UI/UX mejorada** con animaciones y efectos

### 🔧 Tecnologías
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 8.2 + SQLite3
- **Servidor**: Nginx + PHP-FPM
- **Contenedor**: Docker + Docker Compose
- **Librerías**: Chart.js, Lucide Icons

## Instalación y Despliegue

### Requisitos Previos
- Docker
- Docker Compose

### Desarrollo Local
```bash
# Clonar repositorio
git clone <repository-url>
cd cifrado-web

# Iniciar contenedor
docker-compose up -d --build

# Acceder al sitio
# Frontend: http://localhost:8069
# Admin: http://localhost:8069/admin.php
```

### Despliegue en Servidor Ubuntu
```bash
# Instalar Docker
sudo apt update
sudo apt install docker.io docker-compose
sudo systemctl enable docker
sudo systemctl start docker

# Clonar y desplegar
git clone <repository-url>
cd cifrado-web
docker-compose up -d --build

# Configurar firewall
sudo ufw allow 8069/tcp
sudo ufw reload
```

## Administración

### Panel de Admin
- **URL**: `/admin.php`
- **Usuario**: `admin`
- **Contraseña**: `cifrado2025!` (cambiar en producción)

### Funcionalidades del Admin
- Ver todos los contactos con paginación
- Estadísticas (total, hoy, esta semana)
- Enlaces directos para responder emails
- Interfaz responsive

### Base de Datos
- **Archivo**: `data/contacts.db` (SQLite)
- **Backup**: El archivo se puede copiar directamente
- **Persistencia**: Volumen Docker mapeado a `./data`

### Estructura de la BD
```sql
CREATE TABLE contacts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    email TEXT NOT NULL,
    empresa TEXT,
    telefono TEXT,
    mensaje TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address TEXT,
    user_agent TEXT
);
```

## Configuración de Email

### Opción 1: Servidor SMTP Local
El formulario usa `mail()` de PHP. Para configurar SMTP:

```bash
# Instalar postfix (en el servidor)
sudo apt install postfix
sudo systemctl enable postfix
```

### Opción 2: Servicio Externo (Recomendado)
Modificar `contact.php` para usar servicios como:
- SendGrid
- Mailgun  
- Amazon SES

## Seguridad

### Medidas Implementadas
- Validación de entrada en frontend y backend
- Protección CSRF
- Rate limiting por IP
- Headers de seguridad en Nginx
- Acceso denegado a archivos sensibles
- Autenticación simple para admin

### Configuración de Producción
1. Cambiar contraseña de admin en `admin.php`
2. Configurar HTTPS con certificado SSL
3. Configurar backup automático de BD
4. Monitorear logs de Nginx

## Comandos Útiles

### Mantenimiento
```bash
# Ver logs
docker-compose logs -f web

# Reiniciar
docker-compose restart web

# Actualizar
git pull origin main
docker-compose up -d --build

# Backup de base de datos
cp data/contacts.db backup/contacts_$(date +%Y%m%d).db

# Acceder al contenedor
docker exec -it cifrado-web sh
```

### Desarrollo
```bash
# Ejecutar solo con Nginx (sin PHP)
docker run -p 8069:80 -v $(pwd):/usr/share/nginx/html nginx:alpine

# Validar configuración Nginx
docker exec cifrado-web nginx -t
```

## Estructura del Proyecto

```
cifrado-web/
├── index.html              # Página principal
├── contact.php             # Backend del formulario
├── admin.php               # Panel de administración
├── css/styles.css          # Estilos principales
├── js/main.js              # JavaScript principal
├── images/                 # Imágenes optimizadas
│   ├── case-study-*.svg    # Casos de estudio
│   └── team-cifrado.svg    # Imagen del equipo
├── data/                   # Base de datos (creada automáticamente)
│   └── contacts.db         # SQLite database
├── Dockerfile              # Configuración Docker
├── docker-compose.yml      # Orquestación
├── nginx.conf              # Configuración Nginx
├── sitemap.xml             # SEO sitemap
└── robots.txt              # SEO robots
```

## SEO Implementado

### Meta Tags
- Open Graph para redes sociales
- Twitter Cards
- Meta description optimizada
- Keywords relevantes

### Structured Data
- Schema.org Organization
- Servicios estructurados
- Información de contacto

### Performance
- Compresión Gzip
- Cache de assets estáticos
- Lazy loading de imágenes
- Optimización de fonts

## Soporte

Para soporte técnico o consultas:
- Email: info@cifrado.com
- Panel Admin: `/admin.php`

# Web App Acreditaciones - Turismo Nacional

Aplicación web móvil en CakePHP para la gestión de acreditaciones del campeonato de Turismo Nacional.

## Características

- **Mobile-First Design**: Optimizado para dispositivos móviles desde 320px
- **PWA (Progressive Web App)**: Funcionalidad offline y instalable
- **Autenticación JWT**: Sistema seguro de autenticación
- **Códigos QR**: Generación y gestión de códigos QR de acreditación
- **Gestión de Equipos**: Información de equipos y miembros
- **Historial**: Seguimiento de participaciones y estadísticas
- **Promociones**: Sistema de promociones personalizadas
- **Modo Offline**: Funcionalidad limitada sin conexión

## Stack Tecnológico

- **Backend**: CakePHP 5
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Base de Datos**: MySQL
- **Autenticación**: JWT tokens
- **PWA**: Service Workers, Web App Manifest
- **Diseño**: Mobile-first, responsive

## Instalación

### Requisitos

- PHP 8.1 o superior
- MySQL 5.7 o superior
- Composer
- Servidor web (Apache/Nginx)

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <repository-url>
   cd acreditaciones_webapp
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar la base de datos**
   - Crear base de datos MySQL
   - Copiar `config/app_local.php.example` a `config/app_local.php`
   - Configurar credenciales de base de datos

4. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   # Editar .env con tus configuraciones
   ```

5. **Configurar permisos**
   ```bash
   chmod -R 755 tmp/
   chmod -R 755 logs/
   ```

6. **Configurar servidor web**
   - Apuntar document root a `webroot/`
   - Configurar URL rewriting para CakePHP

## Configuración

### API Configuration

Configurar en `config/app_local.php`:

```php
'Api' => [
    'baseUrl' => 'https://api.turismonacional.com/api/v1',
    'timeout' => 30,
    'jwt' => [
        'secret' => 'your-jwt-secret',
        'algorithm' => 'HS256',
        'expiration' => 3600,
    ],
],
```

### PWA Configuration

El archivo `webroot/manifest.json` contiene la configuración PWA:

- Nombre de la aplicación
- Iconos en diferentes tamaños
- Colores del tema
- Modo de visualización
- Accesos directos

## Estructura del Proyecto

```
webapp_acreditaciones/
├── config/                 # Configuración de la aplicación
├── src/
│   ├── Controller/         # Controladores
│   ├── Service/           # Servicios de negocio
│   └── Model/             # Modelos de datos
├── templates/             # Vistas y plantillas
│   ├── layout/           # Layouts principales
│   ├── element/          # Elementos reutilizables
│   ├── Auth/             # Vistas de autenticación
│   ├── Dashboard/        # Vistas del dashboard
│   ├── Profile/          # Vistas de perfil
│   ├── Team/             # Vistas de equipo
│   ├── History/          # Vistas de historial
│   └── Promotions/       # Vistas de promociones
├── webroot/              # Archivos públicos
│   ├── css/              # Estilos CSS
│   ├── js/               # JavaScript
│   ├── img/              # Imágenes
│   ├── manifest.json     # PWA manifest
│   └── sw.js            # Service Worker
└── tests/                # Pruebas unitarias
```

## Funcionalidades

### Autenticación

- Login con DNI y contraseña
- Validación de email para nuevos usuarios
- Recuperación de contraseña
- Gestión de sesiones JWT

### Dashboard

- Estado actual de acreditación
- Acceso rápido al código QR
- Promociones destacadas
- Información del usuario

### Código QR

- Generación automática de códigos QR
- Datos del usuario y equipo
- Guardado en galería
- Compartir código QR

### Gestión de Equipos

- Información del líder del equipo
- Lista de miembros permanentes
- Estados de acreditación
- Estadísticas del equipo

### Historial

- Participaciones en carreras
- Estadísticas de asistencia
- Filtros por año y circuito
- Datos de rendimiento

### Promociones

- Ofertas especiales
- Descuentos exclusivos
- Beneficios para miembros
- Notificaciones de nuevas promociones

## API Endpoints

La aplicación consume los siguientes endpoints:

```
POST /api/v1/auth/login
POST /api/v1/auth/validate-email
POST /api/v1/auth/recover-password
GET  /api/v1/user/profile
GET  /api/v1/user/status
GET  /api/v1/user/qr
GET  /api/v1/user/team
GET  /api/v1/user/history
GET  /api/v1/user/promotions
```

## PWA Features

### Service Worker

- Cache de archivos estáticos
- Cache de respuestas API
- Funcionalidad offline
- Background sync
- Push notifications

### Offline Functionality

- Cache de datos críticos
- Cola de acciones offline
- Sincronización automática
- Indicadores de estado

### Installable

- Manifest.json configurado
- Iconos en múltiples tamaños
- Accesos directos
- Modo standalone

## Desarrollo

### Estructura de Controladores

```php
class DashboardController extends AppController
{
    public function index(): void
    {
        // Lógica del dashboard
    }
    
    public function apiStatus(): Response
    {
        // Endpoint API para estado
    }
}
```

### Servicios

```php
class ApiService
{
    public function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        // Comunicación con API principal
    }
}
```

### Templates

```php
// templates/Dashboard/index.php
<?php $this->assign('title', 'Dashboard'); ?>
<div class="dashboard-container">
    <!-- Contenido del dashboard -->
</div>
```

## Testing

```bash
# Ejecutar pruebas unitarias
vendor/bin/phpunit

# Ejecutar pruebas con coverage
vendor/bin/phpunit --coverage-html coverage/
```

## Deployment

### Producción

1. Configurar `debug = false` en `config/app_local.php`
2. Configurar base de datos de producción
3. Configurar variables de entorno
4. Optimizar assets
5. Configurar SSL/HTTPS
6. Configurar cache de producción

### Docker

```dockerfile
FROM php:8.1-apache
COPY . /var/www/html/
RUN composer install --no-dev --optimize-autoloader
```

## Contribución

1. Fork el proyecto
2. Crear rama para feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver `LICENSE` para más detalles.

## Soporte

Para soporte técnico o preguntas:

- Email: soporte@turismonacional.com
- Documentación: [docs.turismonacional.com](https://docs.turismonacional.com)
- Issues: [GitHub Issues](https://github.com/turismonacional/acreditaciones-webapp/issues)

## Changelog

### v1.0.0
- Lanzamiento inicial
- Autenticación JWT
- Dashboard principal
- Gestión de códigos QR
- PWA básica
- Funcionalidad offline

---

**Desarrollado para Turismo Nacional** 🏁

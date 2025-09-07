# Prompt de Desarrollo - Web App Acreditaciones

## Objetivo
Crear una aplicación web móvil en CakePHP que permita a participantes del campeonato de Turismo Nacional gestionar su proceso de acreditación, visualizar estado, historial y recibir promociones personalizadas mediante consumo de API REST.

## Stack Tecnológico
- **Frontend:** CakePHP 5
- **Backend:** API REST en CakePHP existente
- **Base de Datos:** MySQL existente
- **Autenticación:** JWT tokens
- **Diseño:** Mobile-first, PWA capabilities

## Estructura de Archivos a Crear

### Directorio Principal
```
webapp_acreditaciones/
├── config/
│   ├── app.php
│   ├── routes.php
│   └── bootstrap.php
├── src/
│   ├── Controller/
│   ├── Service/
│   └── Model/
├── templates/
│   ├── layout/
│   └── element/
└── webroot/
    ├── css/
    ├── js/
    └── img/
```

### Controllers a Crear

#### src/Controller/AuthController.php
- `login()` - Login con DNI + contraseña
- `validateEmail()` - Validación primera vez
- `recoverPassword()` - Recuperación por email
- `logout()` - Cierre de sesión

#### src/Controller/DashboardController.php
- `index()` - Pantalla principal con estado actual
- `qr()` - Visualización código QR personal

#### src/Controller/ProfileController.php
- `index()` - Ver perfil personal
- `edit()` - Editar datos personales
- `changePassword()` - Cambiar contraseña

#### src/Controller/TeamController.php
- `index()` - Información del equipo
- `members()` - Lista staff permanente

#### src/Controller/HistoryController.php
- `index()` - Historial participaciones
- `statistics()` - Estadísticas personales

#### src/Controller/PromotionsController.php
- `index()` - Promociones disponibles

### Services a Crear

#### src/Service/ApiService.php
- `makeRequest()` - HTTP requests a API principal
- `get()`, `post()`, `put()`, `delete()` - Métodos HTTP
- `handleResponse()` - Procesamiento respuestas
- `handleErrors()` - Manejo errores API

#### src/Service/AuthService.php
- `authenticate()` - Validación JWT
- `refreshToken()` - Renovación tokens
- `getUser()` - Datos usuario actual
- `isAuthenticated()` - Estado autenticación

#### src/Service/QrService.php
- `generateQr()` - Generación código QR
- `getUserQrData()` - Datos para QR personal

#### src/Service/CacheService.php
- `set()`, `get()`, `delete()` - Manejo caché
- `storeOfflineData()` - Datos offline

### Templates a Crear

#### templates/layout/app.php
- Layout base con navigation bottom
- Meta tags PWA
- CSS/JS includes
- Header responsive

#### templates/Auth/
- `login.php` - Formulario login DNI
- `validate_email.php` - Completar perfil
- `recover_password.php` - Recuperación

#### templates/Dashboard/
- `index.php` - Dashboard principal
- `qr.php` - Pantalla QR código

#### templates/Profile/
- `index.php` - Ver perfil
- `edit.php` - Editar perfil

#### templates/Team/
- `index.php` - Información equipo

#### templates/History/
- `index.php` - Historial participaciones

#### templates/Promotions/
- `index.php` - Lista promociones

#### templates/element/
- `navigation.php` - Bottom navigation
- `status_indicator.php` - Indicadores estado
- `promotion_card.php` - Tarjetas promoción

### Assets a Crear

#### webroot/css/
- `app.css` - Estilos principales mobile-first
- `components.css` - Componentes reutilizables
- `pwa.css` - Estilos PWA específicos

#### webroot/js/
- `app.js` - JavaScript principal
- `auth.js` - Funciones autenticación
- `offline.js` - Funcionalidad offline
- `qr.js` - Manejo códigos QR

#### webroot/img/
- `logo.png` - Logo aplicación
- `icons/` - Iconos PWA diferentes tamaños

### Configuración a Crear

#### config/routes.php
```php
Router::scope('/', function (RouteBuilder $builder) {
    // Auth routes
    $builder->connect('/login', ['controller' => 'Auth', 'action' => 'login']);
    $builder->connect('/validate-email', ['controller' => 'Auth', 'action' => 'validateEmail']);
    
    // Protected routes
    $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
    $builder->connect('/qr', ['controller' => 'Dashboard', 'action' => 'qr']);
    $builder->connect('/profile', ['controller' => 'Profile', 'action' => 'index']);
    $builder->connect('/team', ['controller' => 'Team', 'action' => 'index']);
    $builder->connect('/history', ['controller' => 'History', 'action' => 'index']);
    $builder->connect('/promotions', ['controller' => 'Promotions', 'action' => 'index']);
});
```

#### config/app.php
- Configuración API endpoints
- JWT settings
- Cache configuration
- Database connection

### PWA Files a Crear

#### webroot/manifest.json
- Configuración PWA
- Icons, colors, display mode
- Start URL, scope

#### webroot/sw.js
- Service Worker
- Cache strategies
- Offline functionality

## Funcionalidades Core por Archivo

### AuthController
- Login: DNI + contraseña → JWT token
- Email validation: Primera vez usuarios
- Password recovery: Por email verificado
- Session management: JWT handling

### DashboardController
- Estado actual: Visual acreditación status
- QR generation: Código personal único
- Promociones: Destacar ofertas disponibles

### ProfileController
- Datos personales: DNI, email, teléfono
- Configuraciones: Notificaciones, contraseña
- Account management: Gestión cuenta

### TeamController
- Líder info: Datos contacto líder
- Staff members: Solo miembros permanentes
- Status indicators: Estados visuales equipo

### HistoryController
- Participaciones: Lista cronológica carreras
- Statistics: Carreras asistidas, porcentajes
- Filters: Por año, circuito, tipo

### ApiService
- HTTP client: Comunicación API principal
- Error handling: Manejo errores conexión
- Authentication: Headers JWT automáticos
- Response processing: Normalización datos

## Referencias de Diseño
- **Wireframes:** Archivo `wireframes.html` en proyecto
- **Navigation:** Bottom fixed navigation
- **Colors:** Verde=Activo, Amarillo=Pendiente, Rojo=Inactivo
- **Mobile-first:** Responsive design desde 320px

## Autenticación JWT
- **Login endpoint:** POST /api/v1/auth/login
- **Token storage:** localStorage con refresh
- **Header format:** Authorization: Bearer {token}
- **Auto refresh:** Antes de expiración

## Endpoints API a Consumir
```
POST /api/v1/auth/login
GET /api/v1/user/profile
GET /api/v1/user/status
GET /api/v1/user/qr
GET /api/v1/user/team
GET /api/v1/user/history
GET /api/v1/user/promotions
```

## Estructura Responsive
- **Mobile:** 320px-768px (principal)
- **Tablet:** 768px-1024px
- **Desktop:** 1024px+ (secundario)

## Componentes Reutilizables
- Status indicators (verde/amarillo/rojo)
- Navigation bottom fixed
- Cards promociones
- List items historial/equipo
- QR display component
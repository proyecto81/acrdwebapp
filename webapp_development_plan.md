# Plan de Desarrollo - Web App Acreditaciones

## 1. Finalidad de la Web App

### Objetivo Principal
Crear una aplicación web móvil que permita a los participantes del campeonato de Turismo Nacional gestionar de forma autónoma su proceso de acreditación, visualizar su estado, historial y recibir promociones personalizadas.

### Problemas que Resuelve
- **Reducción del tiempo de pre-acreditación** para líderes de equipo
- **Autonomía total** para participantes en consulta de estado
- **Agilización del proceso** en puestos de acreditación mediante QR
- **Canal directo de marketing** para promociones y ventas
- **Eliminación de consultas manuales** repetitivas

### Beneficiarios
- **Participantes:** Acceso inmediato a su información y QR personal
- **Líderes:** Menos tiempo en gestión administrativa y consultas
- **Organización:** Proceso más eficiente y canal de ventas directo

## 2. Arquitectura Técnica

### Stack Tecnológico
- **Frontend:** CakePHP (para mantener consistencia con sistema principal)
- **Backend:** API REST desarrollada en CakePHP existente
- **Base de Datos:** MySQL (misma BD del sistema de acreditaciones)
- **Diseño:** Mobile-first, CSS Grid/Flexbox, PWA capabilities
- **Autenticación:** JWT tokens

### Separación de Responsabilidades
- **Web App:** Solo cliente/frontend que consume datos
- **Sistema Principal:** Maneja toda la lógica de negocio y datos
- **API:** Puente de comunicación entre ambos sistemas

## 3. Funcionalidades Core

### Autenticación
- **Login:** DNI + contraseña
- **Primera validación:** Email obligatorio
- **Recuperación:** Por email verificado
- **Sesiones:** JWT con refresh token

### Dashboard Principal
- **Estado actual:** Visual del estado de acreditación
- **QR personal:** Acceso rápido (máximo 2 taps)
- **Promociones:** Ofertas personalizadas destacadas
- **Información de carrera:** Próxima fecha y circuito

### Mi Equipo
- **Líder:** Datos de contacto
- **Staff permanente:** Solo miembros fijos (no invitados temporales)
- **Estados:** Visual por cada miembro del core team

### Historial Personal
- **Estadísticas:** Carreras asistidas, porcentaje de asistencia
- **Lista cronológica:** Todas las participaciones históricas
- **Filtros:** Por año, circuito, tipo de acreditación

### Mi Perfil
- **Datos personales:** DNI, email, teléfono
- **Configuraciones:** Notificaciones, contraseña
- **Gestión de cuenta:** Cierre de sesión

## 4. Diseño y UX

### Referencias Visuales
- **Wireframes:** Disponibles en archivo `wireframes.html` del proyecto
- **Diseño:** Mobile-first, bottom navigation, estados visuales claros
- **Colores:** Verde=Activo, Amarillo=Pendiente, Rojo=Inactivo
- **PWA:** Funcionalidad offline para QR y datos básicos

### Navegación
- **Bottom navigation** fijo en todas las pantallas
- **QR accesible** desde dashboard y navegación directa
- **Flujo intuitivo** sin más de 3 niveles de profundidad

## 5. Estructura de Desarrollo

### Directorio Sugerido
```
webapp_acreditaciones/
├── config/
├── src/
│   ├── Controller/
│   ├── Model/
│   ├── View/
│   └── Service/ (para comunicación con API)
├── webroot/
│   ├── css/
│   ├── js/
│   └── img/
└── templates/
    ├── Auth/
    ├── Dashboard/
    ├── Profile/
    └── Layout/
```

### Componentes Principales

#### Controllers
- **AuthController:** Login, registro, validación email
- **DashboardController:** Pantalla principal, estado actual
- **ProfileController:** Gestión de perfil personal
- **TeamController:** Información del equipo
- **HistoryController:** Historial de participaciones
- **QrController:** Generación y visualización de QR

#### Services
- **ApiService:** Comunicación con API del sistema principal
- **AuthService:** Gestión de tokens JWT
- **QrService:** Generación de códigos QR
- **CacheService:** Manejo de datos offline

#### Models (Locales)
- **User:** Datos del usuario logueado
- **Team:** Información del equipo
- **Accreditation:** Estado de acreditaciones
- **History:** Historial de participaciones

## 6. Características Técnicas Especiales

### PWA (Progressive Web App)
- **Service Worker:** Para funcionalidad offline
- **Manifest:** Para instalación en dispositivos
- **Caché inteligente:** QR y datos críticos disponibles sin conexión

### Seguridad
- **JWT Authentication:** Tokens con expiración
- **Rate Limiting:** Prevención de abuso de API
- **Validación de entrada:** Sanitización de todos los inputs
- **HTTPS:** Obligatorio para funcionalidad PWA

### Performance
- **Lazy Loading:** Carga diferida de imágenes y componentes
- **Compresión:** Assets optimizados para móviles
- **Caché HTTP:** Headers adecuados para recursos estáticos

## 7. Fases de Desarrollo

### Fase 1: MVP Básico
1. Sistema de autenticación (DNI + email)
2. Dashboard con estado actual
3. Visualización de QR personal
4. Estructura base de navegación

### Fase 2: Funcionalidades Core
1. Historial de participaciones
2. Información del equipo
3. Gestión básica de perfil
4. Implementación PWA

### Fase 3: Características Avanzadas
1. Sistema de promociones
2. Notificaciones push
3. Funcionalidad offline completa
4. Analytics básicos

### Fase 4: Optimización
1. Performance optimizations
2. UX improvements basado en feedback
3. Integración con sistema de ventas
4. Métricas de uso avanzadas

## 8. Consideraciones de Implementación

### Comunicación con API
- **Timeouts:** Configurar límites adecuados
- **Error Handling:** Manejo elegante de errores de conexión
- **Retry Logic:** Reintentos automáticos para requests fallidos
- **Offline Fallback:** Datos básicos disponibles sin conexión

### Responsive Design
- **Breakpoints:** Mobile-first, tablet, desktop
- **Touch-friendly:** Botones y elementos táctiles adecuados
- **Performance móvil:** Optimizado para conexiones lentas

### Testing
- **Unit Tests:** Para lógica de negocio crítica
- **Integration Tests:** Para comunicación con API
- **User Acceptance Tests:** Flujos principales de usuario
- **Performance Tests:** Tiempos de carga y responsividad

## 9. Métricas de Éxito

### Técnicas
- Tiempo de carga < 3 segundos
- Disponibilidad > 99%
- Errores de API < 1%

### Negocio
- Reducción > 50% en consultas manuales
- Adopción > 70% de usuarios activos
- Conversión promociones > 15%

### Usuario
- Satisfacción > 4.5/5
- Tiempo promedio para ver QR < 10 segundos
- Retención mensual > 80%
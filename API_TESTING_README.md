# 🔗 Guía de Pruebas de API - Acreditaciones TN

Esta guía explica cómo usar las herramientas de prueba de API integradas en CakePHP para verificar la conectividad con la API externa.

## 📋 Herramientas Disponibles

### 1. **Controlador Web** (`/test`)
- **URL:** `http://localhost/acrdwebapp/test`
- **Características:** Interfaz web amigable para ejecutar pruebas
- **Uso:** Ideal para pruebas manuales y debugging visual

### 2. **Comando de Consola** (`bin/cake test_api`)
- **Comando:** `bin/cake test_api`
- **Características:** Ejecución desde línea de comandos
- **Uso:** Ideal para CI/CD, scripts automatizados y pruebas rápidas

### 3. **Tests Unitarios** (PHPUnit)
- **Comando:** `vendor/bin/phpunit tests/TestCase/Integration/ApiConnectionTest.php`
- **Características:** Tests automatizados con PHPUnit
- **Uso:** Ideal para testing continuo y validación de regresiones

## 🚀 Uso de las Herramientas

### Interfaz Web

```bash
# Acceder a la interfaz de pruebas
http://localhost/acrdwebapp/test
```

**Funcionalidades:**
- ✅ Test de conectividad básica
- 🔐 Test de login con credenciales
- 🔒 Test de endpoints protegidos
- 🚀 Test completo automatizado
- 🧹 Limpieza de sesión de pruebas

### Comando de Consola

```bash
# Test básico
bin/cake test_api

# Test con URL personalizada
bin/cake test_api --url=http://localhost:8080/api/v1

# Test con timeout personalizado
bin/cake test_api --timeout=60

# Test con información detallada
bin/cake test_api --verbose
```

**Opciones disponibles:**
- `--url, -u`: URL base de la API
- `--timeout, -t`: Timeout en segundos
- `--verbose, -v`: Mostrar información detallada

### Tests Unitarios

```bash
# Ejecutar todos los tests de integración
vendor/bin/phpunit tests/TestCase/Integration/

# Ejecutar solo el test de API
vendor/bin/phpunit tests/TestCase/Integration/ApiConnectionTest.php

# Ejecutar con cobertura
vendor/bin/phpunit --coverage-html coverage tests/TestCase/Integration/ApiConnectionTest.php
```

## ⚙️ Configuración

### Variables de Entorno

Crea un archivo `.env` en la raíz del proyecto:

```env
# API Testing Configuration
API_TEST_BASE_URL=http://localhost/acreditacionestn2025/api/v1
API_TEST_DNI=12345678
API_TEST_PASSWORD=password123
```

### Configuración de la API

El archivo `config/test_api.php` contiene toda la configuración:

```php
return [
    'ApiTest' => [
        'baseUrl' => 'http://localhost/acreditacionestn2025/api/v1',
        'testCredentials' => [
            'dni' => '12345678',
            'password' => 'password123'
        ],
        'timeouts' => [
            'connectivity' => 30,
            'login' => 30,
            'endpoints' => 30
        ]
        // ... más configuración
    ]
];
```

## 📊 Interpretación de Resultados

### ✅ Éxito
- **Conectividad:** API responde correctamente
- **Login:** Autenticación exitosa con token válido
- **Endpoints:** Todos los endpoints protegidos funcionan
- **Performance:** Tiempos de respuesta aceptables

### ❌ Errores Comunes

#### Error de Conectividad
```
❌ Error de conexión: Connection refused
```
**Solución:** Verificar que la API esté ejecutándose

#### Error 404
```
❌ Error de conectividad (HTTP 404)
```
**Solución:** Verificar las rutas de la API

#### Error 500
```
❌ Error en login (HTTP 500)
```
**Solución:** Verificar la base de datos y configuración del servidor

#### Error de Autenticación
```
❌ Error en login (HTTP 401)
```
**Solución:** Verificar las credenciales de prueba

## 🔧 Solución de Problemas

### 1. API No Responde
```bash
# Verificar que la API esté ejecutándose
curl http://localhost/acreditacionestn2025/api/v1/promotions/active

# Verificar logs del servidor
tail -f /var/log/apache2/error.log
```

### 2. Problemas de CORS
```php
// En la API, configurar CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### 3. Timeouts
```bash
# Aumentar timeout en las pruebas
bin/cake test_api --timeout=60
```

### 4. Credenciales Incorrectas
```bash
# Verificar usuarios en la base de datos
mysql -u root -p acreditaciones_tn
SELECT * FROM users WHERE dni = '12345678';
```

## 📈 Monitoreo y Logging

### Logs de Pruebas
```bash
# Ver logs de pruebas
tail -f logs/api_test.log

# Ver logs de CakePHP
tail -f logs/debug.log
```

### Métricas de Performance
- **Tiempo de respuesta:** < 5 segundos
- **Disponibilidad:** > 99%
- **Tasa de éxito:** > 95%

## 🔄 Integración con CI/CD

### GitHub Actions
```yaml
name: API Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run API tests
        run: bin/cake test_api
```

### Docker
```dockerfile
# Dockerfile para pruebas
FROM php:8.1-cli
COPY . /app
WORKDIR /app
RUN composer install
CMD ["bin/cake", "test_api"]
```

## 📚 Referencias

- [CakePHP Testing](https://book.cakephp.org/4/en/development/testing.html)
- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [HTTP Client de CakePHP](https://book.cakephp.org/4/en/core-libraries/httpclient.html)

## 🆘 Soporte

Si encuentras problemas:

1. **Revisa los logs** en `logs/api_test.log`
2. **Verifica la configuración** en `config/test_api.php`
3. **Ejecuta las pruebas** con `--verbose` para más detalles
4. **Consulta la documentación** de la API externa

---

**Nota:** Estas herramientas están diseñadas para desarrollo y testing. No uses credenciales reales en las pruebas.

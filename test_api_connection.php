<?php
/**
 * Test de Conexión API - Acreditaciones TN
 * Script PHP para probar la conectividad con la API local
 */

// Configuración
$apiBaseUrl = 'http://localhost/acreditacionestn2025/api/v1';
$testResults = [];

// Función para hacer peticiones HTTP
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge([
            'Content-Type: application/json',
            'Accept: application/json'
        ], $headers),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Función para formatear resultados
function formatResult($test, $success, $message, $data = null) {
    return [
        'test' => $test,
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Test 1: Conectividad básica
echo "<h2>🔗 Test de Conexión API - Acreditaciones TN</h2>\n";
echo "<p><strong>API Base URL:</strong> {$apiBaseUrl}</p>\n";
echo "<p><strong>WebApp URL:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>\n";
echo "<hr>\n";

echo "<h3>📡 Test 1: Conectividad Básica</h3>\n";
$result = makeRequest($apiBaseUrl . '/promotions/active');
if ($result['http_code'] == 200 && !$result['error']) {
    $data = json_decode($result['response'], true);
    echo "<p style='color: green;'>✅ Conectividad OK! (HTTP {$result['http_code']})</p>\n";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>\n";
    $testResults[] = formatResult('Conectividad Básica', true, 'OK', $data);
} else {
    echo "<p style='color: red;'>❌ Error de conectividad (HTTP {$result['http_code']})</p>\n";
    if ($result['error']) {
        echo "<p>Error cURL: {$result['error']}</p>\n";
    }
    echo "<pre>{$result['response']}</pre>\n";
    $testResults[] = formatResult('Conectividad Básica', false, "HTTP {$result['http_code']}", $result['error']);
}

// Test 2: Login
echo "<h3>🔐 Test 2: Login</h3>\n";
$loginData = [
    'dni' => '12345678',
    'password' => 'password123'
];

$result = makeRequest($apiBaseUrl . '/auth/login', 'POST', $loginData);
if ($result['http_code'] == 200 && !$result['error']) {
    $data = json_decode($result['response'], true);
    if (isset($data['success']) && $data['success']) {
        echo "<p style='color: green;'>✅ Login exitoso!</p>\n";
        echo "<p><strong>Token:</strong> " . substr($data['data']['token'], 0, 50) . "...</p>\n";
        echo "<pre>" . json_encode($data['data']['user'], JSON_PRETTY_PRINT) . "</pre>\n";
        $testResults[] = formatResult('Login', true, 'OK', $data['data']);
        $authToken = $data['data']['token'];
    } else {
        echo "<p style='color: red;'>❌ Error en login: " . ($data['message'] ?? 'Error desconocido') . "</p>\n";
        $testResults[] = formatResult('Login', false, $data['message'] ?? 'Error desconocido');
    }
} else {
    echo "<p style='color: red;'>❌ Error de conexión en login (HTTP {$result['http_code']})</p>\n";
    if ($result['error']) {
        echo "<p>Error cURL: {$result['error']}</p>\n";
    }
    echo "<pre>{$result['response']}</pre>\n";
    $testResults[] = formatResult('Login', false, "HTTP {$result['http_code']}", $result['error']);
}

// Test 3: Endpoints protegidos (si tenemos token)
if (isset($authToken)) {
    echo "<h3>🔒 Test 3: Endpoints Protegidos</h3>\n";
    
    $protectedEndpoints = [
        '/user/profile' => 'Perfil de Usuario',
        '/user/status' => 'Estado de Acreditación',
        '/user/team' => 'Información del Equipo',
        '/user/history' => 'Historial de Participaciones'
    ];
    
    $headers = ['Authorization: Bearer ' . $authToken];
    
    foreach ($protectedEndpoints as $endpoint => $description) {
        echo "<h4>{$description}</h4>\n";
        $result = makeRequest($apiBaseUrl . $endpoint, 'GET', null, $headers);
        
        if ($result['http_code'] == 200 && !$result['error']) {
            $data = json_decode($result['response'], true);
            echo "<p style='color: green;'>✅ {$endpoint} OK! (HTTP {$result['http_code']})</p>\n";
            echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>\n";
            $testResults[] = formatResult($description, true, 'OK', $data);
        } else {
            echo "<p style='color: red;'>❌ Error en {$endpoint} (HTTP {$result['http_code']})</p>\n";
            if ($result['error']) {
                echo "<p>Error cURL: {$result['error']}</p>\n";
            }
            echo "<pre>{$result['response']}</pre>\n";
            $testResults[] = formatResult($description, false, "HTTP {$result['http_code']}", $result['error']);
        }
        echo "<hr>\n";
    }
} else {
    echo "<h3>🔒 Test 3: Endpoints Protegidos</h3>\n";
    echo "<p style='color: orange;'>⚠️ No se pudo obtener token de autenticación. Saltando tests de endpoints protegidos.</p>\n";
}

// Resumen de resultados
echo "<h3>📊 Resumen de Resultados</h3>\n";
$successCount = 0;
$totalCount = count($testResults);

foreach ($testResults as $result) {
    $status = $result['success'] ? '✅' : '❌';
    $color = $result['success'] ? 'green' : 'red';
    echo "<p style='color: {$color};'>{$status} {$result['test']}: {$result['message']}</p>\n";
    if ($result['success']) $successCount++;
}

echo "<hr>\n";
echo "<p><strong>Resultado Final:</strong> {$successCount}/{$totalCount} pruebas exitosas</p>\n";

if ($successCount == $totalCount) {
    echo "<p style='color: green; font-weight: bold;'>🎉 ¡Todas las pruebas pasaron! La API está funcionando correctamente.</p>\n";
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Algunas pruebas fallaron. Revisa la configuración de la API.</p>\n";
}

// Información de debugging
echo "<h3>🔧 Información de Debugging</h3>\n";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
echo "<p><strong>cURL Version:</strong> " . curl_version()['version'] . "</p>\n";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>\n";
echo "<p><strong>Request Time:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

// Verificar extensiones PHP necesarias
echo "<h4>Extensiones PHP:</h4>\n";
$requiredExtensions = ['curl', 'json'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $color = $loaded ? 'green' : 'red';
    $status = $loaded ? '✅' : '❌';
    echo "<p style='color: {$color};'>{$status} {$ext}: " . ($loaded ? 'Cargada' : 'No cargada') . "</p>\n";
}

// Sugerencias de solución de problemas
echo "<h3>💡 Sugerencias de Solución de Problemas</h3>\n";
echo "<ul>\n";
echo "<li>Verifica que la API esté ejecutándose en <code>http://localhost/acreditacionestn2025/</code></li>\n";
echo "<li>Comprueba que el servidor web (Apache/Nginx) esté funcionando</li>\n";
echo "<li>Verifica que las rutas de la API estén configuradas correctamente</li>\n";
echo "<li>Comprueba los logs de error del servidor web</li>\n";
echo "<li>Verifica que la base de datos esté configurada y funcionando</li>\n";
echo "<li>Comprueba que las dependencias de PHP estén instaladas (cURL, JSON)</li>\n";
echo "</ul>\n";

// Enlaces útiles
echo "<h3>🔗 Enlaces Útiles</h3>\n";
echo "<ul>\n";
echo "<li><a href='{$apiBaseUrl}/promotions/active' target='_blank'>Probar endpoint público</a></li>\n";
echo "<li><a href='test_api_connection.html' target='_blank'>Test desde navegador</a></li>\n";
echo "<li><a href='http://localhost/acreditacionestn2025/' target='_blank'>API Principal</a></li>\n";
echo "</ul>\n";
?>

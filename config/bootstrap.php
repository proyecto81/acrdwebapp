<?php
declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Database\TypeFactory;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ConsoleErrorHandler;
use Cake\Error\ErrorHandler;
use Cake\Http\ServerRequest;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\Utility\Inflector;
use Cake\Utility\Security;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('ROOT', dirname(__DIR__));
define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('APP', ROOT . DS . 'src' . DS);
define('APP_DIR', 'src');
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
define('TMP', ROOT . DS . 'tmp' . DS);
define('CONFIG', ROOT . DS . 'config' . DS);
define('CACHE', TMP . 'cache' . DS);
define('LOGS', TMP . 'logs' . DS);
define('RESOURCES', ROOT . DS . 'resources' . DS);

require ROOT . DS . 'vendor' . DS . 'autoload.php';

if (!class_exists(Configure::class)) {
    throw new RuntimeException('CakePHP core could not be found. Check the value of CAKE_CORE_INCLUDE_PATH in ROOT/config/bootstrap.php. It should point to the root of your CakePHP core installation and your vendor directory.');
}

Configure::config('default', new PhpConfig());
Configure::load('app', 'default', false);

mb_internal_encoding(Configure::read('App.encoding'));

ini_set('intl.default_locale', Configure::readOrFail('App.defaultLocale'));

date_default_timezone_set(Configure::readOrFail('App.defaultTimezone'));

ini_set('session.cookie_httponly', 1);

Security::setSalt(Configure::readOrFail('Security.salt'));

Cache::setConfig(Configure::consume('Cache'));
ConnectionManager::setConfig(Configure::consume('Datasources'));
TransportFactory::setConfig(Configure::consume('EmailTransport'));
Log::setConfig(Configure::consume('Log'));
Mailer::setConfig(Configure::consume('Email'));

Inflector::rules('uninflected', ['.*[nN]ews']);
Inflector::rules('irregular', ['person' => 'people', 'man' => 'men', 'child' => 'children', 'sex' => 'sexes', 'move' => 'moves', 'cow' => 'kine', 'zombie' => 'zombies']);

if (PHP_SAPI === 'cli') {
    (new ConsoleErrorHandler(Configure::read('Error')))->register();
} else {
    (new ErrorHandler(Configure::read('Error')))->register();
}

if (file_exists(CONFIG . 'bootstrap_local.php')) {
    require CONFIG . 'bootstrap_local.php';
}

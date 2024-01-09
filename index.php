<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

!defined('BASE_PATH') && define('BASE_PATH', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

require BASE_PATH . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\ScanCommand;
use App\Command\PsyshCommand;

(function () {
    // run app
    $application = new Application();
    $application->setName("wxapkg");
    $application->add(new ScanCommand());
    // $application->add(new PsyshCommand());
    $application->setDefaultCommand('scan', true);
    $application->setVersion('1.0.0');
    return $application->run();
})();

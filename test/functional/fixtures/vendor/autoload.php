<?php

// 1) Include root composer autoloader
require_once __DIR__ . '/../../../../vendor/autoload.php';

// 2) Emulate project-scope autoloader
$autoloader = new \Nette\Loaders\RobotLoader();

$autoloader->setTempDirectory(sys_get_temp_dir());

$autoloader->addDirectory(__DIR__ . '/../lib');
$autoloader->addDirectory(__DIR__ . '/../apps/cache/lib');
$autoloader->addDirectory(__DIR__ . '/../apps/frontend/lib');
$autoloader->addDirectory(__DIR__ . '/../apps/i18n/lib');

$autoloader->register();

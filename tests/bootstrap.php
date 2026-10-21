<?php
// Bootstrap for tests

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../src/common/libs/bCMS/projectFinance.php';

// Set timezone to Europe/London for DST tests
date_default_timezone_set('Europe/London');

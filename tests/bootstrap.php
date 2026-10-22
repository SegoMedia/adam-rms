<?php

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load MockMysqliDb
require_once __DIR__ . '/MockMysqliDb.php';

// Initialize global variables
$GLOBALS['DBLIB'] = new MockMysqliDb();
$GLOBALS['CONFIG'] = [
    'ROOTURL' => 'http://localhost',
    'TIMEZONE' => 'UTC',
    'AWS_S3_REGION' => 'us-east-1',
    'AWS_S3_BROWSER_ENDPOINT' => 'http://s3.localhost',
    'AWS_S3_ENDPOINT_PATHSTYLE' => 'Disabled',
    'AWS_S3_KEY' => 'key',
    'AWS_S3_SECRET' => 'secret',
    'AWS_S3_BUCKET' => 'bucket'
];

// Mock AUTH object
$GLOBALS['AUTH'] = new stdClass();
$GLOBALS['AUTH']->data = [
    'instance' => [
        'instances_config_currency' => 'GBP',
        'instances_id' => 1
    ],
    'user' => [
        'users_userid' => 1
    ]
];
$GLOBALS['AUTH']->login = true;

// Mock Config class
class Config {
    public array $CONFIG_STRUCTURE = [];
    public array $CONFIG_MISSING_VALUES = [];
    protected array $DBCACHE = [];

    public function __construct()
    {
        $this->DBCACHE = $GLOBALS['CONFIG'];
    }

    public function getConfigArray(): array {
        return $this->DBCACHE;
    }

    public function get(string $key) {
        return isset($this->DBCACHE[$key]) ? $this->DBCACHE[$key] : null;
    }
}
$GLOBALS['CONFIGCLASS'] = new Config();

// Load the classes to be tested
require_once __DIR__ . '/../src/common/libs/bCMS/projectFinance.php';
require_once __DIR__ . '/../src/common/libs/bCMS/bCMS.php';

// Mock bCMS global
$GLOBALS['bCMS'] = new bCMS();

// Default timezone for date/DST-sensitive tests (pre-existing suite from the fork)
date_default_timezone_set('Europe/London');

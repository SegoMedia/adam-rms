<?php

class TestCase {
    protected $passed = 0;
    protected $failed = 0;

    public function assertEquals($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            $this->failed++;
            $msg = "Assertion failed: Expected " . var_export($expected, true) . ", got " . var_export($actual, true);
            if ($message) $msg .= " ($message)";
            echo "\n  [FAIL] $msg\n";
            return false;
        }
        $this->passed++;
        return true;
    }

    public function run() {
        $className = get_class($this);
        echo "Running tests in $className...\n";
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (strpos($method, 'test') === 0) {
                echo "- $method: ";
                $initialFailed = $this->failed;
                try {
                    $this->$method();
                    if ($this->failed === $initialFailed) {
                        echo "OK\n";
                    }
                } catch (Exception $e) {
                    $this->failed++;
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }
        echo "Summary: $this->passed passed, $this->failed failed.\n\n";
        return $this->failed === 0;
    }
}

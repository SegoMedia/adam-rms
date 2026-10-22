<?php

class MockMysqliDb
{
    public $calls = [];
    public $returnValues = [];
    public $lastError = '';

    public function __call($name, $arguments)
    {
        $this->calls[] = ['method' => $name, 'args' => $arguments];

        if (isset($this->returnValues[$name])) {
            $return = $this->returnValues[$name];
            if (is_callable($return)) {
                return call_user_func_array($return, $arguments);
            }
            return $return;
        }

        return $this; // Return self for chaining by default
    }

    public function where($prop, $value = null, $operator = null, $cond = 'AND')
    {
        $this->calls[] = ['method' => 'where', 'args' => func_get_args()];
        return $this;
    }

    public function getone($tableName, $columns = '*')
    {
        $this->calls[] = ['method' => 'getone', 'args' => func_get_args()];
        if (isset($this->returnValues['getone'])) {
            return $this->returnValues['getone'];
        }
        return null;
    }

    public function get($tableName, $numRows = null, $columns = '*')
    {
        $this->calls[] = ['method' => 'get', 'args' => func_get_args()];
        if (isset($this->returnValues['get'])) {
            return $this->returnValues['get'];
        }
        return [];
    }

    public function getValue($tableName, $column, $limit = 1)
    {
        $this->calls[] = ['method' => 'getValue', 'args' => func_get_args()];
        if (isset($this->returnValues['getValue'])) {
            return $this->returnValues['getValue'];
        }
        return null;
    }

    public function insert($tableName, $insertData)
    {
        $this->calls[] = ['method' => 'insert', 'args' => func_get_args()];
        if (isset($this->returnValues['insert'])) {
            return $this->returnValues['insert'];
        }
        return true;
    }

    public function update($tableName, $tableData, $numRows = null)
    {
        $this->calls[] = ['method' => 'update', 'args' => func_get_args()];
        if (isset($this->returnValues['update'])) {
            return $this->returnValues['update'];
        }
        return true;
    }

    public function inc($value)
    {
        // inc usually returns an array or object representing the increment operation
        // For simplicity in mocking, we can just return the value or a special object
        return ["INC" => $value];
    }

    public function escape($string)
    {
        return addslashes($string);
    }

    public function getLastError() {
        return $this->lastError;
    }

    // Helper to set return values for tests
    public function setReturnValue($method, $value)
    {
        $this->returnValues[$method] = $value;
    }

    public function reset() {
        $this->calls = [];
        $this->returnValues = [];
        $this->lastError = '';
    }
}

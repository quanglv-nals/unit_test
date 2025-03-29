<?php

namespace App\Services;

/**
 * Mock class for database operations to be used in unit testing.
 */
class DB
{
    protected static $mockData = [];
    protected $table;
    protected $conditions = [];

    /**
     * Set mock data for a specific table.
     *
     * @param string $table Table name
     * @param array $data Mock data as an array of associative arrays
     */
    public static function setMockData(string $table, array $data)
    {
        self::$mockData[$table] = $data;
    }

    /**
     * Clear all mock data.
     */
    public static function clearMockData()
    {
        self::$mockData = [];
    }

    /**
     * Get a table instance for querying.
     *
     * @param string $table Table name
     * @return self
     */
    public static function table(string $table)
    {
        $instance = new self();
        $instance->table = $table;
        return $instance;
    }

    /**
     * Add a where condition.
     *
     * @param string $column Column name
     * @param mixed $value Value to match
     * @return self
     */
    public function where(string $column, $value)
    {
        $this->conditions[$column] = $value;
        return $this;
    }

    /**
     * Get the first matching row based on the conditions.
     *
     * @return object|null Returns an object representing the row or null if not found.
     */
    public function first()
    {
        if (!isset(self::$mockData[$this->table])) {
            return null;
        }

        foreach (self::$mockData[$this->table] as $row) {
            $match = true;
            foreach ($this->conditions as $column => $value) {
                if (!isset($row[$column]) || $row[$column] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return (object) $row;
            }
        }

        return null;
    }
}

<?php

namespace Tests\Unit;

use App\Exceptions\DatabaseException;
use PHPUnit\Framework\TestCase;

class DatabaseExceptionTest extends TestCase
{
    public function testDatabaseExceptionMessage()
    {
        $exception = new DatabaseException('Database Error occurred');
        $this->assertEquals('Database Error occurred', $exception->getMessage());
    }

    public function testDatabaseExceptionCode()
    {
        $exception = new DatabaseException('Database Error occurred', 400);
        $this->assertEquals(400, $exception->getCode());
    }
}

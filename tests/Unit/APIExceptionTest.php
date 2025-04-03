<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Exceptions\APIException;

class APIExceptionTest extends TestCase
{
    public function testAPIExceptionMessage()
    {
        $exception = new APIException('API Error occurred');
        $this->assertEquals('API Error occurred', $exception->getMessage());
    }

    public function testAPIExceptionCode()
    {
        $exception = new APIException('API Error occurred', 500);
        $this->assertEquals(500, $exception->getCode());
    }
}

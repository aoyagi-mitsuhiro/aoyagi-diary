<?php

use PHPUnit\Framework\TestCase;
use Aoyagi\AoyagiDiary\model\AuthRepository;

class AuthRepositoryTest extends TestCase
{
    public function test_getUserByUsername()
    {
        $mockPdo = $this->createMock(\PDO::class);
        $mockStmt = $this->createMock(\PDOStatement::class);

        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(['username' => 'aoyagi', 'id' => 1]);

        $repo = new AuthRepository($mockPdo);

        $result = $repo->getUserByUsername('aoyagi');

        $this->assertNotNull($result);
        $this->assertEquals('aoyagi', $result['username']);
    }
}

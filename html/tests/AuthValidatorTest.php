<?php

namespace Aoyagi\AoyagiDiary\tests;

use PHPUnit\Framework\TestCase;
use Aoyagi\AoyagiDiary\validator\AuthValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class AuthValidatorTest extends TestCase
{
    private AuthValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new AuthValidator('valid_token');
    }

    public function test_validate_validInput(): void
    {
        $errors = $this->validator->validate('testUsername', 'testPW');
        $this->assertEmpty($errors);
    }


    #[DataProvider('invalidFieldsProvider')]
    public function test_invalid_data(string $username, string $password, string $expectedError): void
    {
        $errors = $this->validator->validate($username, $password);
        $this->assertContains($expectedError, $errors);
    }

    public static function invalidFieldsProvider(): array
    {
        return [
            'wrong user name' => [
                '<            >    !!!!!      ',
                'testPW',
                'id can only contain letters, numbers, and underscores.'
            ],
            'empty fields' => [
                '',
                '',
                'id, pw are required.'
            ],
        ];
    }


    #[DataProvider('tooLongDataProvider')]
    public function test_too_long_data(string $username, string $password, string $expectedError): void
    {
        $errors = $this->validator->validate($username, $password);
        $this->assertContains($expectedError, $errors);
    }

    public static function tooLongDataProvider(): array
    {
        return [
            'too long username' => [
                'abcdefgqweabcdefgqweabcdefgqweabcdefgqweabcdefgqw51',
                'testPW',
                'length of id should be less than 50.'
            ],
            'too long password' => [
                'testUsername',
                'abcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwerabcdegqwer',
                'length of pw should be less than 255.'
            ],
        ];
    }
}

<?php

namespace Aoyagi\AoyagiDiary\tests;

use PHPUnit\Framework\TestCase;
use Aoyagi\AoyagiDiary\validator\DiaryValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class DiaryValidatorTest extends TestCase
{
    private DiaryValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new DiaryValidator();
    }

    public function test_validate_validInput(): void
    {
        $errors = $this->validator->validate('My Diary', '2024-01-01', 'Today was a good day.');
        $this->assertEmpty($errors);
    }

    public static function emptyFieldProvider(): array
    {
        return [
            'all fields empty' => ['', '', '', 'All fields are required'],
            'title empty' => ['', '2024-01-01', 'Today was a good day.', 'All fields are required'],
            'date empty' => ['My Diary', '', 'Today was a good day.', 'All fields are required'],
            'contents empty' => ['My Diary', '2024-01-01', '', 'All fields are required'],
            'title spaceFilled fields' => [str_repeat(' ', 10), '2024-01-01', 'Today was a good day.', 'All fields are required'],
            'date spaceFilled fields' => ['My Diary', str_repeat(' ', 10), 'Today was a good day.', 'All fields are required'],
            'contents spaceFilled fields' => ['My Diary', '2024-01-01', str_repeat(' ', 10), 'All fields are required'],
        ];
    }

    #[DataProvider('emptyFieldProvider')]
    public function test_validate_emptyFields(string $title, string $date, string $contents, string $expectedError): void
    {
        $errors = $this->validator->validate($title, $date, $contents);
        $this->assertContains($expectedError, $errors);
    }


    public static function invalidDateProvider(): array
    {
        $tomorrow = (new \DateTime())->modify('+1 day')->format('Y-m-d');
        return [
            'invalid date format' => ['My Diary', '2026-02-1100', 'Today was a good day.', 'Invalid date: 2026-02-1100'],
            'non-existent date' => ['My Diary', '2024-02-30', 'Today was a good day.', 'Invalid date: 2024-02-30'],
            'invalid date' => ['My Diary', $tomorrow, 'Today was a good day.', 'Date cannot be in the future'],
            'wrong separator' => ['My Diary', '2024/01/01', 'Today was a good day.', 'Invalid date format'],
        ];
    }

    #[DataProvider('invalidDateProvider')]
    public function test_validate_invalidDates(string $title, string $date, string $contents, string $expectedError): void
    {
        $errors = $this->validator->validate($title, $date, $contents);
        $this->assertContains($expectedError, $errors);
    }


    public static function xssAndSqlInjectionProvider(): array
    {
        return [
            'xss attack' => ['<script>alert("XSS")</script>', '2024-01-01', 'Today was a good day.'],
            'sql injection in title' => ['title; DROP TABLE diaries;', '2024-01-01', 'content'],
            'sql injection in date' => ['title', '2024-01-01; DROP TABLE diaries;', 'content', 'Invalid date format'],
            'sql injection in contents' => ['title', '2024-01-01', 'content; DROP TABLE diaries;'],
        ];
    }

    #[DataProvider('xssAndSqlInjectionProvider')]
    public function test_validate_xssAndSqlInjection(string $title, string $date, string $contents, ?string $expectedError = null): void
    {
        $errors = $this->validator->validate($title, $date, $contents);
        if ($expectedError) {
            $this->assertContains($expectedError, $errors);
        } else {
            $this->assertEmpty($errors);
        }
    }

    public static function boundaryTitleProvider(): array
    {
        return [
            'exactly 255 characters' => [str_repeat('a', 255), '2024-01-01', 'Today was a good day.'],
            'hiragana exactly 255 characters' => [str_repeat('あ', 255), '2024-01-01', 'Today was a good day.'],
            '256 characters' => [str_repeat('a', 256), '2024-01-01', 'Today was a good day.', 'Title must be 255 characters or less'],
        ];
    }

    #[DataProvider('boundaryTitleProvider')]
    public function test_validate_boundaryTitle(string $title, string $date, string $contents, ?string $expectedError = null): void
    {
        $errors = $this->validator->validate($title, $date, $contents);
        if ($expectedError) {
            $this->assertContains($expectedError, $errors);
        } else {
            $this->assertEmpty($errors);
        }
    }
}

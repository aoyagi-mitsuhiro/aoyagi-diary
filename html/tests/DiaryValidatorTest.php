<?php

namespace Aoyagi\AoyagiDiary\Tests;

use PHPUnit\Framework\TestCase;
use Aoyagi\AoyagiDiary\DiaryValidator;

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

    public function test_validate_emptyFields(): void
    {
        $errors = $this->validator->validate('', '', '');
        $this->assertContains('All fields are required', $errors);
    }

    public function test_validate_invalidDate(): void
    {
        $errors = $this->validator->validate('My Diary', '2026-02-1100', 'Today was a good day.');
        $this->assertContains('Invalid date: 2026-02-1100', $errors);
    }

    public function test_validate_nonExistentDate(): void
    {
        $errors = $this->validator->validate('My Diary', '2024-02-30', 'Today was a good day.');
        $this->assertContains('Invalid date: 2024-02-30', $errors);
    }

    public function test_validate_futureDate(): void
    {
        $tomorrow = (new \DateTime())->modify('+1 day')->format('Y-m-d');
        $errors = $this->validator->validate('title', $tomorrow, 'content');
        $this->assertContains('Date cannot be in the future', $errors);
    }

    public function test_validate_longTitle(): void
    {
        $longTitle = str_repeat('a', 256);
        $errors = $this->validator->validate($longTitle, '2024-01-01', 'Today was a good day.');
        $this->assertContains('Title must be 255 characters or less', $errors);
    }

    public function test_validate_xssAttack(): void
    {
        $errors = $this->validator->validate('<script>alert("XSS")</script>', '2024-01-01', 'Today was a good day.');
        $this->assertEmpty($errors);
    }

    public function test_validate_sqlInjection(): void
    {
        $errors = $this->validator->validate('title', '2024-01-01', 'content; DROP TABLE diaries;');
        $this->assertEmpty($errors);
    }

    public function test_validate_sqlInjectionInTitle(): void
    {
        $errors = $this->validator->validate('title; DROP TABLE diaries;', '2024-01-01', 'content');
        $this->assertEmpty($errors);
    }

    public function test_validate_sqlInjectionInDate(): void
    {
        $errors = $this->validator->validate('title', '2024-01-01; DROP TABLE diaries;', 'content');
        $this->assertContains('Invalid date format', $errors);
    }

    public function test_validate_spaceFilled(): void
    {
        $errors = $this->validator->validate(str_repeat(' ', 10), '2024-01-01', str_repeat(' ', 10));
        $this->assertContains('All fields are required', $errors);
    }

    public function test_validate_exactly255CharactersTitle(): void
    {
        $title = str_repeat('a', 255);
        $errors = $this->validator->validate($title, '2024-01-01', 'Today was a good day.');
        $this->assertEmpty($errors);
    }

    public function test_validate_JapaneseCharacters(): void
    {
        $japaneseTitle = str_repeat('あ', 255);
        $errors = $this->validator->validate($japaneseTitle, '2024-01-01', '今日は良い日でした。');
        $this->assertEmpty($errors);
    }

    public function test_validate_dateWithWrongSeparator(): void
    {
        $errors = $this->validator->validate('My Diary', '2024/01/01', 'Today was a good day.');
        $this->assertContains('Invalid date format', $errors);
    }
}

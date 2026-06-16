<?php

namespace Aoyagi\AoyagiDiary\validator;

class DiaryValidator
{
    public function validate(string $title, string $date, string $contents): array
    {
        $errorMessages = [];

        // 必須項目のチェック
        if (trim($title) === '' || trim($date) === '' || trim($contents) === '') {
            $errorMessages[] = 'All fields are required';
        }

        // タイトルの長さのチェック
        if (mb_strlen($title) > 255) {
            $errorMessages[] = 'Title must be 255 characters or less';
        }

        // 日付の形式と妥当性をチェック            
        $dateParts = explode('-', $date);
        if (count($dateParts) !== 3) {
            $errorMessages[] = 'Invalid date format';
            return $errorMessages;
        }

        $year = (int)$dateParts[0];
        $month = (int)$dateParts[1];
        $day = (int)$dateParts[2];

        // 存在しない日付のチェック
        if (!checkdate($month, $day, $year)) {
            $errorMessages[] = "Invalid date: {$date}";
            return $errorMessages;
        }

        // 日付の形式のチェック
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errorMessages[] = 'Invalid date format';
            return $errorMessages;
        }

        $inputDate = new \DateTime($date);
        $today = new \DateTime();

        // 日付が未来でないことのチェック
        if ($inputDate > $today) {
            $errorMessages[] = 'Date cannot be in the future';
        }

        return $errorMessages;
    }
}

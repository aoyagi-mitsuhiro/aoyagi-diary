<?php

namespace Aoyagi\AoyagiDiary\validator;

class AuthValidator
{

    public function validate(string $username, string $password): array
    {
        $errorMessages = [];

        if (trim($username) === '' || trim($password) === '') {
            $errorMessages[] = 'id, pw are required.';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errorMessages[] = 'id can only contain letters, numbers, and underscores.';
        }

        if (strlen($username) > 50) {
            $errorMessages[] = 'length of id should be less than 50.';
        }
        if (strlen($password) > 255) {
            $errorMessages[] = 'length of pw should be less than 255.';
        }

        return $errorMessages;
    }
}

<?php

namespace App\Utils;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use function Symfony\Component\String\u;

class Validator
{
    public function validateUsername(?string $username): string
    {
        if (empty($username)) {
            throw new InvalidArgumentException('Le nom utilisateur ne peut pas être vide');
        }

        if (1 !== preg_match('/^[a-z_]+$/', $username)) {
            throw new InvalidArgumentException('Un utilisateur doit contenir uniquement des caractères latins minuscules et des traits de soulignement.');
        }

        return $username;
    }

    public function validatePassword(?string $plainPassword): string
    {
        if (empty($plainPassword)) {
            throw new InvalidArgumentException('Le mot de passe ne peut pas êtrze vide');
        }

        if (u($plainPassword)->trim()->length() < 6) {
            throw new InvalidArgumentException('Le mot de passe doit au moin contenir 6 caractères');
        }

        return $plainPassword;
    }

    public function validateEmail(?string $email): string
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Le mail ne peut pas être vide');
        }

        if (null === u($email)->indexOf('@')) {
            throw new InvalidArgumentException('Le mail ne semble pas être à un format valide');
        }

        return $email;
    }

    public function validateFullName(?string $fullName): string
    {
        if (empty($fullName)) {
            throw new InvalidArgumentException('Le nom complet ne peut pas être vide');
        }

        return $fullName;
    }
}

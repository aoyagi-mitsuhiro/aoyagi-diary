<?php

use DI\ContainerBuilder;
use Aoyagi\AoyagiDiary\Database\Database;
use Aoyagi\AoyagiDiary\model\AuthRepository;
use Aoyagi\AoyagiDiary\model\DiaryRepository;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$builder = new ContainerBuilder();
$builder->addDefinitions([
    PDO::class => function () {
        return Database::getConnection();
    },
    Environment::class => function () {
        $loader = new FilesystemLoader(__DIR__ . '/../src/views');
        return new Environment($loader);
    },
    AuthRepository::class => \DI\autowire(),
    DiaryRepository::class => \DI\autowire()
]);

return $builder->build();

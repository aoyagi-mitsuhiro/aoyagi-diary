<?php

use DI\ContainerBuilder;
use Aoyagi\AoyagiDiary\Database\Database;
use Aoyagi\AoyagiDiary\model\AuthRepository;
use Aoyagi\AoyagiDiary\model\DiaryRepository;
use Aoyagi\AoyagiDiary\service\CsvDownloadService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Aoyagi\AoyagiDiary\service\DiaryDownloadInterface;
use Aoyagi\AoyagiDiary\service\ExcelDownloadService;

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
    DiaryRepository::class => \DI\autowire(),
    DiaryDownloadInterface::class => function () {
        $format = $_GET['format'] ?? 'csv';

        if ($format === 'excel') {
            return new ExcelDownloadService();
        }

        return new CsvDownloadService();
    }
]);

return $builder->build();

<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

passthru('php bin/console doctrine:database:drop --if-exists --force --env=test');
passthru('php bin/console doctrine:database:create --env=test');
passthru('php bin/console doctrine:migration:migrate -n --env=test');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

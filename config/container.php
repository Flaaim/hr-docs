<?php

declare(strict_types=1);

use DI\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/dependencies.php');

$builder->useAutowiring(true);
$builder->useAttributes(false);

return $builder->build();

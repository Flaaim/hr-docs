<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$file = (getenv('ENV_FILE')) ? "dev.env" : ".env";
if (file_exists(__DIR__ . '/common/env/' . $file)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/common/env/', $file);
    $dotenv->load();
} else {
    exit(".Env file not found");
}

$aggregator = new ConfigAggregator([
    new PhpFileProvider(__DIR__ . '/common/*.php'),
]);


return $aggregator->getMergedConfig();

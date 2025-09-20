<?php

declare(strict_types=1);

use App\Http\Frontend\FrontendUrlGenerator;
use Psr\Container\ContainerInterface;

return [
  FrontendUrlGenerator::class => function (ContainerInterface $container) {
      return new FrontendUrlGenerator($_ENV['APP_PATH']);
  }
];

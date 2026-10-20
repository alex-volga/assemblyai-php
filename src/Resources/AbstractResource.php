<?php

declare(strict_types=1);

namespace AssemblyAI\Resources;

use AssemblyAI\Config;
use AssemblyAI\Http\HttpClient;

abstract class AbstractResource
{
    public function __construct(
        protected readonly HttpClient $http,
        protected readonly Config $config,
    ) {
    }
}

<?php

namespace grafkit;

use Castor\Attribute\AsTask;
use Grafkit\App;

#[AsTask]
function cache(): void
{
    App::getInstance()->refreshDashboardCache();
}

#[AsTask]
function find(string $regex): void
{
    $results = App::getInstance()->searchDashboards("$regex");
    foreach ($results as $result) {
        echo ("{$result->path}: {$result->pattern}" . PHP_EOL);
    }
}

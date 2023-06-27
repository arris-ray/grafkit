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
function find(string $search): void
{
    $results = App::getInstance()->searchDashboards("$search");
    foreach ($results as $result) {
        echo ("{$result->url}: {$result->pattern}" . PHP_EOL);
    }
}

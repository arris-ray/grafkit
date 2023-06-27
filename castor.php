<?php

namespace grafkit;

use Castor\Attribute\AsTask;
use Grafkit\App;

#[AsTask]
function cache(string $label): void
{
    $label = ($label !== "") ? $label : null;
    App::getInstance()->refreshDashboardCache($label);
}

#[AsTask]
function find(string $search): void
{
    $results = App::getInstance()->searchDashboards("$search");
    foreach ($results as $result) {
        echo ("{$result->label}: {$result->url}: {$result->pattern}" . PHP_EOL);
    }
}

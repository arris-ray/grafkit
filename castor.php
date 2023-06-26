<?php

namespace grafkit;

use Castor\Attribute\AsTask;

#[AsTask]
function test(): void {
    echo "Hello, world!";
}

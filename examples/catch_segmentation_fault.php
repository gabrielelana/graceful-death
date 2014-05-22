<?php

require __DIR__ . "/../vendor/autoload.php";

// The output will be:
//
// Segmentation fault in 3... 2... 1...
// You didn't notice but it happend! ;-)

GracefulDeath::around(function() {
    echo "Segmentation fault in 3... 2... 1...\n";
    die_violently();
})
->afterViolentDeath(
    "You didn't notice but it happend! ;-)\n"
)
->run();

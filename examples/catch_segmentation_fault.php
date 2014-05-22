<?php

require __DIR__ . "/../vendor/autoload.php";

// NOTE: To run this you need to have installed violent-death
// see https://github.com/gabrielelana/violent-death

// Sorry but I don't want to have an explicit dependency
// with violent-death (wich is not so easy to install) only
// to run this example

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

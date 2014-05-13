<?php

require __DIR__ . "/../vendor/autoload.php";

// The output will be:
//
// Hey! You know, it's not cool to overflow the stack!

GracefulDeath::around(function() {
    // Avoid to crash for memory exhaustion
    ini_set('memory_limit', -1);

    countToInfinite();
})
->afterViolentDeath("Hey! You know, it's not cool to overflow the stack!\n")
->run();

function countToInfinite($counter = 0) {
    return countToInfinite($counter + 1);
}

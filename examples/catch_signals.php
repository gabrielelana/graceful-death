<?php

require __DIR__ . "/../vendor/autoload.php";

// The output will be:
//
// CTRL-C to terminate
// I will live forever!!!
// I will live forever!!!
// I will live forever!!!
// ...
// Maybe not... :-(

GracefulDeath::around(function($life) {

    echo "CTRL-C to terminate\n";

    while (!$life->askedToStop()) {
        echo "I will live forever!!!\n";
        // Let's pretend to do something useful :-)
        sleep(1);
    }
})
->doNotCaptureOutput()
->catchSignals([SIGTERM, SIGQUIT, SIGINT])
->afterNaturalDeath("Maybe not... :-(\n")
->run();

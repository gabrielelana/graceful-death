<?php

require __DIR__ . "/../../vendor/autoload.php";

GracefulDeath::around(function($life) {
    while (!$life->askedToStop()) {
        usleep(10000);
    }
})
->doNotCaptureOutput()
->catchSignals([SIGTERM, SIGQUIT, SIGINT])
->afterNaturalDeath("Bye Bye")
->run();

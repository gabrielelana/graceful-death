<?php

require __DIR__ . "/../../vendor/autoload.php";

$options = getopt('', ['what:']);

GracefulDeath::around(function() use($options) {
    file_put_contents('php://stdout', $options['what']);
})
->run();

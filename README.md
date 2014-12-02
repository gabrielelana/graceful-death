# Graceful Death [![Build Status](https://travis-ci.org/gabrielelana/graceful-death.svg?branch=master)](https://travis-ci.org/gabrielelana/graceful-death)
As you may know, catching fatal errors in PHP is extremely painful aka nearly impossible. This is a library that (partially) solves this issue

## Usage
```php
<?php

require __DIR__ . "/vendor/autoload.php";

// The output will be:
//
// Yes, I can ;-)

GracefulDeath::around(function() {
    try {
        // Avoid to print the error in order to have clean output, don't try this at home :-)
        error_reporting(E_ALL ^ E_ERROR);
        // Creating an instance of an unknown class will cause a fatal error
        new UnknownClass();
    } catch(Exception $e) {
        // A fatal error is uncatchable
        echo "You cannot catch this, AHAHAHAHA!!!\n";
    }
})
->afterViolentDeath("Yes, I can ;-)\n")
->run();
```

## Scenario
You have a piece of code that potentially can trigger a fatal error and you want to be able to clean up after it or retry it with some policy. With `GracefulDeath` you can put this piece of code in a closure and pass it as a parameter to the `GracefulDeath::around` static method. This static method returns an instance of a builder that let you configure how `GracefulDeath` will behave. With `afterViolentDeath` you can configure an handler that will be called whenever a fatal error is triggered. The handler could be
* An integer: the process will terminate with this integer as status code
* A string: the string will be printed on standard output (like in this example)
* A closure: the closure will be executed and its return value will be used as return value of the `run` method. The closure signature is `function($status, $stdout, $stderr)` where `$status` is the exit status of the code passed to `GracefulDeath::around`

There are a few other method that can be used to configure `GracefulDeath`
* `afterNaturalDeath`: like `afterViolentDeath` but used to configure an handler that will be called only when no errors are triggered
* `afterDeath`: used to configure an handler (like `afterViolentDeath` and `afterNaturalDeath`) that will be called after the code passed to `GracefulDeath::around` is terminated
* `reanimationPolicy`: used to configure the reanimation policy aka something that will be used to decide if the code passed to `GracefulDeath::around` should be executed again after a fatal error. The reanimation policy could be
  * A boolean: if true it will retry forever, if false it will never retry (default)
  * An integer: the number of times the code will be executed. The code will not be execute again if either the code terminates without error or the number of executions exceeds the number passed as argument
  * A closure: if the closure returns true the code will be executed again. The closure signature is `function($status, $attempts, $stdout, $stderr)`
    * `$status`: the exit status of the code passed to `GracefulDeath::around`
    * `$attempts`: how many times the code has been executed (think how many previous lives), starts at `1`
    * `$stdout`: what the code passed to `GracefulDeath::around` printed on `stdout`
    * `$stderr`: what the code passed to `GracefulDeath::around` printed on `stderr`

  There are a few reanimation policies ready to be used
    * `doNotReanimate`: this is the default behaviour so not so useful to use but could be good for documentation or to make the code more explicit
    * `giveMeAnotherChance`: it will reanimate the child process only one time
    * `liveForever`: it will always reanimate the child process. If you use this reanimation policy don't forget also to use `avoidFutileMedicalCare` to avoid to continually reanimate a child process that is broken
* `doNotCaptureOutput`: avoid to capture `stdout` and `stderr`. Note that if output is not captured then it could not be given to the `reanimationPolicy` closure
* `doNotEchoOutput`: discard the (if any) captured output
* `avoidFutileMedicalCare($numberOfFailures, $inAmountOfTime)`: avoid to reanimate a process that died too many times (`$numberOfFailures` default to 6) in a small amount of time (`$inAmountOfTime` default 60 seconds)

For all the options and methods look at the examples or at the tests :smile:

## Use Cases
* You have a long running process that leaks memory (see `examples/memory_leak.php`)
* You can use `graceful-death` as a [supervisor](http://supervisord.org/), for that use `->liveForever()` to always reanimate the child process and don't forget to use `->avoidFutileMedicalCare()` to avoid to continually reanimate a child process that is broken

## How Does It Work?
When `run` is called the process forks, the child process will execute the code passed to `GracefulDeath::around`, the parent process will act as a supervisor of the child. The supervisor will wait until the child dies and will act accordingly to the exit status and the given configuration.

## Gotcha
It only works where `pcntl_*` function are available.

## Self-Promotion
If you like this project, then consider to:
* Star the repository on [GitHub](https://github.com/gabrielelana/graceful-death)
* Follow me on
  * [Twitter](http://twitter.com/gabrielelana)
  * [GitHub](https://github.com/gabrielelana)

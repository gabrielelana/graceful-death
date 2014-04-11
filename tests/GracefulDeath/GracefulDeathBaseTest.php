<?php

abstract class GracefulDeathBaseTest extends \PHPUnit_Framework_TestCase
{
    protected function doSomethingUnharmful()
    {
        return 1 + 1;
    }

    protected function raiseFatalError($doNotReportErrors = true)
    {
        if ($doNotReportErrors) {
            error_reporting(E_ALL ^ E_ERROR);
        }
        // Instance an unknown class cause a fatal error
        new UnknownClass();
    }
}

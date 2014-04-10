<?php


class GracefulDeathTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCatchViolentDeath()
    {
        $result = GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterViolentDeath(function($status) {
            return 'Catched';
        })
        ->run();

        $this->assertEquals('Catched', $result);
    }

    public function testCanCatchNaturalDeath()
    {
        $result = GracefulDeath::around(function() {
            $this->doSomethingUnharmful();
        })
        ->afterNaturalDeath(function($status) {
            return 'Catched';
        })
        ->run();

        $this->assertEquals('Catched', $result);
    }

    public function testNaturalDeathIsDeath()
    {
        $result = GracefulDeath::around(function() {
            $this->doSomethingUnharmful();
        })
        ->afterDeath(function($status) {
            return 'Catched';
        })
        ->run();

        $this->assertEquals('Catched', $result);
    }

    public function testViolentDeathIsDeath()
    {
        $result = GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterDeath(function($status) {
            return 'Catched';
        })
        ->run();

        $this->assertEquals('Catched', $result);
    }


    private function doSomethingUnharmful()
    {
        return 1 + 1;
    }

    private function raiseFatalError()
    {
        error_reporting(E_ALL ^ E_ERROR);
        // Instance an unknown class cause a fatal error
        new UnknownClass();
    }
}

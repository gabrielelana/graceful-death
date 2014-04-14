<?php

class GracefulDeathTest extends GracefulDeathBaseTest
{
    public function setUp()
    {
        $this->caught = null;
    }

    public function testAfterViolentDeathWillCatchAViolentDeath()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterViolentDeath(function($status) {
            $this->caught = 'Caught';
        })
        ->run();

        $this->assertEquals('Caught', $this->caught);
    }

    public function testAfterNaturalDeathWillCatchANaturalDeath()
    {
        GracefulDeath::around(function() {
            $this->doSomethingUnharmful();
        })
        ->afterNaturalDeath(function($status) {
            $this->caught = 'Caught';
        })
        ->run();

        $this->assertEquals('Caught', $this->caught);
    }

    public function testAfterDeathWillCatchANaturalDeath()
    {
        GracefulDeath::around(function() {
            $this->doSomethingUnharmful();
        })
        ->afterDeath(function($status) {
            $this->caught = 'Caught';
        })
        ->run();

        $this->assertEquals('Caught', $this->caught);
    }

    public function testAfterDeathWillCatchAViolentDeath()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterDeath(function($status) {
            $this->caught = 'Caught';
        })
        ->run();

        $this->assertEquals('Caught', $this->caught);
    }

    public function testSayGoodbyeToYourLovedOnceIsAnAlisOfAfterDeathIfYouWhatToBeFunny()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->sayGoodbyeToYourLovedOnce(function($status) {
            $this->caught = 'Caught';
        })
        ->run();

        $this->assertEquals('Caught', $this->caught);
    }

    public function testByDefaultAViolentDeathIsIgnored()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->run();
    }

    public function testByDefaultANaturalDeathIsIgnored()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->run();
    }

    public function testAroundWillReturnWhatIsReturnedByTheDeathHandler()
    {
        $result = GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterDeath(function() {
            return 'Valar Morghulis';
        })
        ->run();

        $this->assertEquals('Valar Morghulis', $result);
    }

    public function testAroundByDefaultReturnsNull()
    {
        $result = GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->run();

        $this->assertNull($result);
    }
}

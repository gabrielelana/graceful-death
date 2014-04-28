<?php

class GracefulDeathTest extends GracefulDeathBaseTest
{
    public function testAfterViolentDeathWillCatchAViolentDeath()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterViolentDeath($this->willBeCalled($this->once()))
        ->afterNaturalDeath($this->willBeCalled($this->never()))
        ->run();
    }

    public function testAfterNaturalDeathWillCatchANaturalDeath()
    {
        GracefulDeath::around(function() {
            $this->doSomethingUnharmful();
        })
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->afterViolentDeath($this->willBeCalled($this->never()))
        ->run();
    }

    public function testAfterDeathWillCatchANaturalDeath()
    {
        GracefulDeath::around(function() {
            $this->doSomethingUnharmful();
        })
        ->afterDeath($this->willBeCalled($this->once()))
        ->run();
    }

    public function testAfterDeathWillCatchAViolentDeath()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterDeath($this->willBeCalled($this->once()))
        ->run();
    }

    public function testSayGoodbyeToYourLovedOnceIsAnAliasOfAfterDeathIfYouWhatToBeFunny()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->sayGoodbyeToYourLovedOnce($this->willBeCalled($this->once()))
        ->run();
    }

    public function testAViolentDeathIsIgnored()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->run();
    }

    public function testANaturalDeathIsIgnored()
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

    public function testAroundReturnsNullWithoutDeathHandlers()
    {
        $result = GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->run();

        $this->assertNull($result);
    }
}

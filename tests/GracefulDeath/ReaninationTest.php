<?php


class ReanimationTest extends GracefulDeathBaseTest
{
    public function testAroundClosureTakesLifeCounter()
    {
        GracefulDeath::around(function($lifeCounter) {
            if ($lifeCounter !== 1) {
                $this->raiseFatalError();
            }
        })
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->run();
    }

    public function testCanBeReanimatedOneTime()
    {
        GracefulDeath::around(function($lifeCounter) {
            if ($lifeCounter === 1) {
                // It will raise a fatal error only the first execution
                $this->raiseFatalError();
            }
        })
        ->reanimationPolicy(GracefulDeath::GIVE_ME_ANOTHER_CHANCE)
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->afterViolentDeath($this->willBeCalled($this->never()))
        ->run();
    }

    public function testCanBeReanimatedMoreThanOneTime()
    {
        $numberOfRetry = 4;
        $result = GracefulDeath::around(function($lifeCounter) use($numberOfRetry) {
            if ($lifeCounter < $numberOfRetry) {
                // It will raise a fatal error only the first $numberOfRetry times
                $this->raiseFatalError();
            }
            $this->doSomethingUnharmful();
        })
        ->reanimationPolicy($numberOfRetry)
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->afterViolentDeath($this->willBeCalled($this->never()))
        ->run();
    }

    public function testCanBeReanimatedWithArbitraryPolicy()
    {
        GracefulDeath::around(function($lifeCounter) {
            if ($lifeCounter < 3) {
                exit(5);
            }
            $this->doSomethingUnharmful();
        })
        ->reanimationPolicy(function($status, $lifeCounter, $output) {
            return $status === 5;
        })
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->afterViolentDeath($this->willBeCalled($this->never()))
        ->run();
    }
}

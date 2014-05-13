<?php


class ReanimationTest extends GracefulDeathBaseTest
{
    public function testAroundClosureTakesLifeToWhichCanAskHowManyLiveYouHaveLived()
    {
        GracefulDeath::around(function($life) {
            if ($life->numberOfPreviousLives() > 1) {
                $this->raiseFatalError();
            }
        })
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->run();
    }

    public function testCanBeReanimatedOneTime()
    {
        GracefulDeath::around(function($life) {
            if ($life->numberOfPreviousLives() === 1) {
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
        $result = GracefulDeath::around(function($life) use($numberOfRetry) {
            if ($life->numberOfPreviousLives() < $numberOfRetry) {
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
        // It will retry for 2ms
        $startedAt = microtime(true);
        GracefulDeath::around(function() {
            if (microtime(true) - $startedAt < 2000) {
                $this->raiseFatalError();
            }
        })
        ->reanimationPolicy(function($status, $attempts, $output) use($startedAt) {
            return microtime(true) - $startedAt > 2000;
        })
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->afterViolentDeath($this->willBeCalled($this->never()))
        ->run();
    }

    public function testCanBeReanimatedForever()
    {
        // It will retry for ever, but after 3 times it will die naturally
        GracefulDeath::around(function($life) {
            if ($life->numberOfPreviousLives() < 3) {
                exit(5);
            }
            $this->doSomethingUnharmful();
        })
        ->reanimationPolicy(true)
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->afterViolentDeath($this->willBeCalled($this->never()))
        ->run();
    }
}

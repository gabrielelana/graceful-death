<?php


class ReanimationTest extends GracefulDeathBaseTest
{
    public function testByDefaultItWillNotBeReanimated()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->afterNaturalDeath($this->willBeCalled($this->never()))
        ->afterViolentDeath($this->willBeCalled($this->once()))
        ->run();
    }

    public function testDoNotReanimateReanimationPolicy()
    {
        GracefulDeath::around(function() {
            $this->raiseFatalError();
        })
        ->doNotReanimate()
        ->afterNaturalDeath($this->willBeCalled($this->never()))
        ->afterViolentDeath($this->willBeCalled($this->once()))
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
        ->reanimationPolicy(1)
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->afterViolentDeath($this->willBeCalled($this->never()))
        ->run();
    }

    public function testGiveMeAnotherChanceReanimationPolicy()
    {
        GracefulDeath::around(function($life) {
            if ($life->numberOfPreviousLives() === 1) {
                // It will raise a fatal error only the first execution
                $this->raiseFatalError();
            }
        })
        ->giveMeAnotherChance()
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

    public function testLiveForeverReanimationPolicy()
    {
        // It will retry for ever, but after 3 times it will die naturally
        GracefulDeath::around(function($life) {
            if ($life->numberOfPreviousLives() < 3) {
                exit(5);
            }
            $this->doSomethingUnharmful();
        })
        ->liveForever()
        ->afterNaturalDeath($this->willBeCalled($this->once()))
        ->afterViolentDeath($this->willBeCalled($this->never()))
        ->run();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadReanimationPolicy()
    {
        GracefulDeath::around(function() { })
            ->reanimationPolicy('Strings are not convertible to a reanimation policy');
    }
}

<?php


class ReanimationTest extends GracefulDeathBaseTest
{
    public function testCanBeReanimatedOneTime()
    {
        $result = GracefulDeath::around(function($lifeCounter) {
            if ($lifeCounter === 1) {
                $this->raiseFatalError();
            } else {
                $this->doSomethingUnharmful();
            }
        })
        ->reanimationPolicy(GracefulDeath::GIVE_ME_ANOTHER_CHANCE)
        ->afterViolentDeath(function($status) {
            return 'Violent';
        })
        ->afterNaturalDeath(function($status) {
            return 'Natural';
        })
        ->run();

        $this->assertEquals('Natural', $result);
    }

    public function testCanBeReanimatedMoreThanOneTime()
    {
        $numberOfRetry = 4;
        $result = GracefulDeath::around(function($lifeCounter) use($numberOfRetry) {
            if ($lifeCounter < $numberOfRetry) {
                $this->raiseFatalError();
            } else {
                $this->doSomethingUnharmful();
            }
        })
        ->reanimationPolicy($numberOfRetry)
        ->afterViolentDeath(function($status) {
            return 'Violent';
        })
        ->afterNaturalDeath(function($status) {
            return 'Natural';
        })
        ->run();

        $this->assertEquals('Natural', $result);
    }

    public function testCanBeReanimatedWithArbitraryPolicy()
    {
        $result = GracefulDeath::around(function($lifeCounter) {
            if ($lifeCounter < 3) {
                exit(5);
            } else {
                $this->raiseFatalError();
            }
        })
        ->reanimationPolicy(function($status, $lifeCounter, $output) {
            return $status === 5;
        })
        ->afterViolentDeath(function($status) {
            return 'Violent';
        })
        ->afterNaturalDeath(function($status) {
            return 'Natural';
        })
        ->run();

        $this->assertEquals('Violent', $result);
    }
}

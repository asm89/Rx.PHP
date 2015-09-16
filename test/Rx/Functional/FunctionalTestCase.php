<?php

namespace Rx\Functional;

use PHPUnit_Framework_ExpectationFailedException;
use Rx\Scheduler\VirtualTimeScheduler;
use Rx\TestCase;
use Rx\Testing\ColdObservable;
use Rx\Testing\HotObservable;
use Rx\Testing\Subscription;
use Rx\Testing\TestScheduler;

abstract class FunctionalTestCase extends TestCase
{
    /** @var  TestScheduler */
    protected $scheduler;

    public function setup()
    {
        $this->scheduler = $this->createTestScheduler();
    }

    public function assertMessages(array $expected, array $recorded)
    {
        if (count($expected) !== count($recorded)) {
            $this->fail(sprintf('Expected message count %d does not match actual count %d.', count($expected), count($recorded)));
        }

        for ($i = 0, $count = count($expected); $i < $count; $i++) {
            if (! $expected[$i]->equals($recorded[$i])) {
                $this->fail($expected[$i] . ' does not equal ' . $recorded[$i]);
            }
        }

        $this->assertTrue(true); // success
    }

    public function assertSubscription(HotObservable $observable, Subscription $expected)
    {
        $subscriptionCount = count($observable->getSubscriptions());

        if ($subscriptionCount === 0) {
            $this->fail('Observable has no subscriptions.');
        }

        if ($subscriptionCount > 1) {
            $this->fail('Observable has more than 1 subscription.');
        }

        list($actual) = $observable->getSubscriptions();

        if ( ! $expected->equals($actual)) {
            $this->fail(sprintf("Expected subscription '%s' does not match actual subscription '%s'", $expected, $actual));
        }

        $this->assertTrue(true); // success
    }

    public function assertSubscriptions(array $expected, array $recorded)
    {
        if (count($expected) !== count($recorded)) {
            $this->fail(sprintf('Expected subscription count %d does not match actual count %d.', count($expected), count($recorded)));
        }

        for ($i = 0, $count = count($expected); $i < $count; $i++) {
            if (! $expected[$i]->equals($recorded[$i])) {
                $this->fail($expected[$i] . ' does not equal ' . $recorded[$i]);
            }
        }

        $this->assertTrue(true); // success
    }

    /**
     * This was taken from https://gist.github.com/VladaHejda/8826707
     *
     * @param callable $callback
     * @param string $expectedException
     * @param null $expectedCode
     * @param null $expectedMessage
     */
    protected function assertException(callable $callback, $expectedException = 'Exception', $expectedCode = null, $expectedMessage = null)
    {
        $expectedException = ltrim((string) $expectedException, '\\');
        if (!class_exists($expectedException) && !interface_exists($expectedException)) {
            $this->fail(sprintf('An exception of type "%s" does not exist.', $expectedException));
        }
        try {
            $callback();
        } catch (\Exception $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $code = $e->getCode();
            $errorMessage = 'Failed asserting the class of exception';
            if ($message && $code) {
                $errorMessage .= sprintf(' (message was %s, code was %d)', $message, $code);
            } elseif ($code) {
                $errorMessage .= sprintf(' (code was %d)', $code);
            }
            $errorMessage .= '.';
            $this->assertInstanceOf($expectedException, $e, $errorMessage);
            if ($expectedCode !== null) {
                $this->assertEquals($expectedCode, $code, sprintf('Failed asserting code of thrown %s.', $class));
            }
            if ($expectedMessage !== null) {
                $this->assertContains($expectedMessage, $message, sprintf('Failed asserting the message of thrown %s.', $class));
            }
            return;
        }
        $errorMessage = 'Failed asserting that exception';
        if (strtolower($expectedException) !== 'exception') {
            $errorMessage .= sprintf(' of type %s', $expectedException);
        }
        $errorMessage .= ' was thrown.';
        $this->fail($errorMessage);
    }


    protected function createColdObservable(array $events)
    {
        return new ColdObservable($this->scheduler, $events);
    }

    protected function createHotObservable(array $events)
    {
        return new HotObservable($this->scheduler, $events);
    }

    protected function createTestScheduler()
    {
        return new TestScheduler();
    }
}

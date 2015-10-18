<?php


namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;


class SkipLastOperator implements OperatorInterface
{

    /** @var integer */
    private $count;

    /** @var Array */
    private $q;


    /**
     * SkipLastOperator constructor.
     * @param $count
     * @throws
     */
    public function __construct($count)
    {
        $this->count = $count;
        if ($this->count < 0) {
            throw new \InvalidArgumentException("Argument Out of Range");
        }
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $this->q = [];
        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer) {
                $this->q[] = $x;
                if (count($this->q) > $this->count) {
                    $observer->onNext(array_shift($this->q));
                }
            },
            function ($e) use ($observer) {
                $observer->onError($e);
            },
            function () use ($observer) {
                $observer->onCompleted();
            }
        ));
    }
}

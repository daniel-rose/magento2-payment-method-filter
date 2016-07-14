<?php

namespace DR\PaymentMethodFilter\Observer;

use DR\PaymentMethodFilter\Model\FilterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class ExcludeDisallowedPaymentMethod implements ObserverInterface
{
    protected $filterList;

    /**
     * Constructor
     *
     * @param FilterInterface[] $filterList
     */
    public function __construct($filterList = [])
    {
        $this->filterList = $filterList;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        if (!$event || !($event instanceof Event)) {
            return;
        }

        $result = $event->getResult();

        if (!$result || !($result instanceof DataObject) || !$result->getIsAvailable()) {
            return;
        }

        $paymentMethod = $event->getMethodInstance();

        if (!$paymentMethod || !($paymentMethod instanceof MethodInterface)) {
            return;
        }

        $quote = $event->getQuote();

        if (!$quote || !($quote instanceof Quote)) {
            return;
        }

        foreach ($this->filterList as $filter) {
            $filter->execute($paymentMethod, $quote, $result);

            if (!$result->getData('is_available')) {
                return;
            }
        }
    }
}
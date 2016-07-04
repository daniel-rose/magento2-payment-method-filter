<?php

namespace DR\PaymentMethodFilter\Model;

use Magento\Framework\DataObject;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

interface FilterInterface
{
    /**
     * Execute
     *
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @param DataObject $result
     */
    public function execute(MethodInterface $paymentMethod, Quote $quote, DataObject $result);
}
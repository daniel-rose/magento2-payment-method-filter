<?php

namespace DR\PaymentMethodFilter\Model;

use Magento\Framework\DataObject;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;

interface FilterInterface
{
    /**
     * Execute
     *
     * @param MethodInterface $paymentMethod
     * @param CartInterface $quote
     * @param DataObject $result
     */
    public function execute(MethodInterface $paymentMethod, CartInterface $quote, DataObject $result);
}
<?php

namespace DR\PaymentMethodFilter\Model\Filter;

use DR\PaymentMethodFilter\Model\FilterInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;

class Customer implements FilterInterface
{
    /**
     * Execute
     *
     * @param MethodInterface $paymentMethod
     * @param CartInterface $quote
     * @param DataObject $result
     *
     * @return void
     */
    public function execute(MethodInterface $paymentMethod, CartInterface $quote, DataObject $result)
    {
        $customer = $quote->getCustomer();

        if (!$customer || !($customer instanceof CustomerInterface) || !$customer->getId()) {
            return;
        }

        $customAttribute = $customer->getCustomAttribute('disallowed_payment_methods');

        if ($customAttribute === null) {
            return;
        }

        $disallowedPaymentMethods = $customAttribute->getValue();

        if ($disallowedPaymentMethods == '') {
            return;
        }

        $disallowedPaymentMethods = explode(',', $disallowedPaymentMethods);
        $result->setData('is_available', !in_array($paymentMethod->getCode(), $disallowedPaymentMethods));
    }
}
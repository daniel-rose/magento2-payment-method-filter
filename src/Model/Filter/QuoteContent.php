<?php

namespace DR\PaymentMethodFilter\Model\Filter;

use Magento\Framework\DataObject;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use DR\PaymentMethodFilter\Model\FilterInterface;

class QuoteContent implements FilterInterface
{
    /**
     * Execute
     *
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @param DataObject $result
     * 
     * @return void
     */
    public function execute(MethodInterface $paymentMethod, Quote $quote, DataObject $result)
    {
        $visibleItems = $quote->getAllVisibleItems();

        foreach ($visibleItems as $visibleItem) {
            $product = $visibleItem->getProduct();

            $customAttribute = $product->getCustomAttribute('disallowed_payment_methods');


            if ($customAttribute === null) {
                continue;
            }
            
            $disallowedPaymentMethods = $customAttribute->getValue();

            if ($disallowedPaymentMethods == '') {
                continue;
            }

            $disallowedPaymentMethods = explode(',', $disallowedPaymentMethods);

            if (in_array($paymentMethod->getCode(), $disallowedPaymentMethods)) {
                $result->setData('is_available', false);
                return;
            }
        }
    }
}
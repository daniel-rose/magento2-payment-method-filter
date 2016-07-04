<?php

namespace DR\PaymentMethodFilter\Model\Filter;

use DR\PaymentMethodFilter\Model\FilterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class Guest implements FilterInterface
{
    const XML_PATH_DISALLOWED_PAYMENT_METHODS_FOR_GUEST = 'checkout/options/disallowed_payment_methods_for_guest';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Execute
     *
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @param DataObject $result
     */
    public function execute(MethodInterface $paymentMethod, Quote $quote, DataObject $result)
    {
        if ($quote->getCustomerId()) {
            return;
        }

        $disallowedPaymentMethods = $this->scopeConfig->getValue(self::XML_PATH_DISALLOWED_PAYMENT_METHODS_FOR_GUEST);

        if ($disallowedPaymentMethods === null || $disallowedPaymentMethods === '') {
            return;
        }

        $disallowedPaymentMethods = explode(',', $disallowedPaymentMethods);
        $result->setData('is_available', !in_array($paymentMethod->getCode(), $disallowedPaymentMethods));
    }
}
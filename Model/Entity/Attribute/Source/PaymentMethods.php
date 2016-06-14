<?php

namespace DR\PaymentMethodFilter\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Payment\Helper\Data;

class PaymentMethods extends AbstractSource
{
    /**
     * @var Data
     */
    protected $paymentData;

    /**
     * Constructor
     * 
     * @param Data $paymentData
     */
    public function __construct(
        Data $paymentData
    )
    {
        $this->paymentData = $paymentData;
    }


    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];
            $paymentMethods = $this->paymentData->getPaymentMethods();
            
            foreach ($paymentMethods as $code => $paymentMethod) {
                if (isset($paymentMethod['title'])) {
                    $label = $paymentMethod['title'];
                } else {
                    $label = $this->paymentData->getMethodInstance($code)->getConfigData('title', null);
                }

                if ($label) {
                    $this->_options[$code] = [
                        'label' => $code . ' - ' . $label,
                        'value' => $code
                    ];
                }
            }

            usort($this->_options, function($a, $b) {
                return strcmp($a['value'], $b['value']);
            });
        }

        return $this->_options;
    }
}
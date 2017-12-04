<?php

namespace DR\PaymentMethodFilter\Test\Unit\Model\Entity\Attribute\Source;

use DR\PaymentMethodFilter\Model\Entity\Attribute\Source\PaymentMethods;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\MethodInterface;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit\Framework\TestCase;

class PaymentMethodsTest extends TestCase
{
    /**
     * @var PaymentMethods
     */
    protected $attributeSourceModel;

    /**
     * @var Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelperMock;

    /**
     * @var MethodInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodInstanceMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $objectManager = new ObjectManager($this);

        $this->paymentHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->methodInstanceMock = $this->getMockBuilder(MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeSourceModel = $objectManager->getObject(PaymentMethods::class, [
            'paymentData' => $this->paymentHelperMock
        ]);
    }

    /**
     * @test
     */
    public function testGeTAllOptions()
    {
        $paymentMethods = [
            'free' => [
                'title' => 'No Payment Information Required',
                'group' => 'offline'
            ],
            'cashondelivery' => [
                'group' => 'offline'
            ]
        ];
        
        $this->methodInstanceMock
            ->expects($this->atLeastOnce())
            ->method('getConfigData')
            ->with('title', null)
            ->willReturn('Cash On Delivery');
        
        $this->paymentHelperMock
            ->expects($this->atLeastOnce())
            ->method('getMethodInstance')
            ->with('cashondelivery')
            ->willReturn($this->methodInstanceMock);

        $allOptions = [
            [
                'label' => 'cashondelivery - Cash On Delivery',
                'value' => 'cashondelivery'
            ],
            [
                'label' => 'free - No Payment Information Required',
                'value' => 'free'
            ]
        ];
        
        $this->paymentHelperMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        
        $this->assertEquals($allOptions, $this->attributeSourceModel->getAllOptions());
    }
}